<?php

namespace Approach\Resource\CoolSpool;

use Approach\Service\Service;
use \Approach\Service\target;
use Approach\Service\CoolSpool\Connector;
use Approach\Render\Node;
use Approach\Resource\Resource;
use \stringable;
use \Approach\Scope;
use \Approach\path;
use \Approach\deploy;
use \Approach\nullstate;

/**
 * The Approach Resource CoolSpool Spooler class
 * ===============================
 * This class is used to define a CoolSpool Spooler resource which can be used to connect to a CoolSpool Spooler.
 * Coordinating with Approach\Service\CoolSpool\Connector and Approach\Resource\CoolSpool\TypeCatalog, this class can be used to
 * discover a CoolSpool Spooler, its Catalogs, fields and accessors.
 * 
 * Essentially, CoolSpool is an Inter-system API connector, navigating Approach APIs by default.
 * Other APIs can be implemented fairly easily by extending the TypeCatalog and specializing for your API's endpoints.
 * 
 * This version will support legacy v1.0 - 1.8 APIs, and the new v2.0 API.
 * v2.0+ APIs support OIDC, SAML and X.509 authentication.
 * v2.0+ APIs are also slated to support OpenAPI 3.0 and GraphQL, ontop of Approach's native API.
 * 
 * @package		Approach
 * @subpackage	Resource
 * @subpackage	CoolSpool
 * @category	CoolSpool Spooler
 * 
 */

class Spooler extends Resource
{
	protected $pool = [];
    protected static $configs = [];
	protected bool $is_connected = false;
	protected bool $has_persistent = false;
	public $connection;
	
    public function __construct(

		protected ?string $scope = null,
        public null|string|stringable|Node $catalog = null,

        // Normal connection details
        public null|string|stringable|Node 	$host    = null,
        public null|string|stringable|Node 	$user    = null,
        public null|string|stringable|Node 	$pass    = null,
        public null|int 					$port    = null,
        public null|string|stringable|Node 	$socket  = null,

        // SSL connection details. Not implemented yet. Will use our Keycloak/Smallstep CA for X.509 and OIDC auth.
		// Will use in mTLS mode if all are set, or TLS mode if only $ssl_ca is set
        public null|string|stringable|Node $ssl_key  	= null,
        public null|string|stringable|Node $ssl_cert  	= null,
        public null|string|stringable|Node $ssl_ca      = null,
        public null|string|stringable|Node $ssl_capath  = null,
        public null|string|stringable|Node $ssl_cipher  = null,

        // Connection options, currently meaningless
        public ?int $timeout        = null,       		// Ex: 5

        // Connection pool options
        public ?bool $persistent        = true,

		// If cURL multi is implemented, then we can use this to skip the connect() call and have pooling work as normal
		public ?bool $skip_connection    = false,
		protected ?bool $is_pooled        = null,

		/*
		 * This node's name in the connected resources graph: Service::$protocols
		 * 
		 * For CoolSpool://spool.my.home/ : spool__my__home  (auto-generated)
		 * For CoolSpool://myhost.home/MyCatalog : myhost__home and MyCatalog
		 * For anything: you can supply a label to override the auto-generated label
		 * 
		 * Reflected in:
		 * 	- generated classes
		 * 	- Resource aspect classes
		 * 	- Resource::find('CoolSpool://MrServer/CoolCollection')  -- relies on Service::$protocols
		 *  - Service::$protocols[CoolSpool][$label][$label][..]
		 */
        public null|string|stringable|Node $label    = null
    )
    {
		// Check if we are in a pooled cluster or not
		if ($this->is_pooled === null){
			$this->is_pooled = $this->check_pooled();
		}
		$scope = $scope ?? Scope::GetDeploy(deploy::remote);

		// Check for any class constants which can be used as defaults
		foreach (get_class_vars(static::class) as $key => $value){
			if (!isset($this->{$key}) && defined(static::class . '::' . $key))
			{
				$this->{$key} = constant(static::class . '::' . $key);
			}
		}

		// Get hostname from scope url
		$scopehost = parse_url($scope, PHP_URL_HOST);

		// Fallback Defaults
		$this->host        = $this->host     ?? $scopehost;;
		// $this->pass        = $this->pass     ?? ini_get('coolspool.default_api_key');
		// $this->port        = $this->port     ?? ini_get('approach.default_port') ?? 776;
		// $this->socket      = $this->socket   ?? ini_get('approach.default_socket') ?? '/var/lib/approach/default.sock';

		$this->label       = $this->label ?? $this->get_fallback_label();
		$this->set_render_id();

		// Check if any SSL options are set; throw an error if unable to connect
		$this->use_ssl = 
			$this->ssl_key || 
			$this->ssl_cert || 
			$this->ssl_ca || 
			$this->ssl_capath || 
			$this->ssl_cipher
		;

		// Check if we are in a pooled cluster or not
		if ($this->is_pooled === null)
		{
			$this->is_pooled = $this->check_pooled();
		}

	
    }

	public function get_fallback_label(){
		// normalize host to safe characters for class name
		$domain = explode('.', $this->host);
		$is_ip = false;

		// If this is an IP address, or . separated number, we need to handle it differently
		foreach ($domain as $part){
			if (is_numeric($part)){
				$is_ip = true;
			}
			else{
				$is_ip = false;
				break;
			}
		}
		if( $is_ip ) return 'ip_' . implode('_', $domain);
		

		// This action is due to the knowledge that domains are generally in reverse order
		// The SOA of com is "rootward" of any example.com SOA, and example.com is "rootward" of www.example.com
		$rev_domain = array_reverse($domain);
		$rev_project_ensemble = array_reverse(
			explode(
				'.',
				Scope::GetDeploy(deploy::ensemble)
			)
		);

		$match = false;
		// Check if we are in a subdomain of the project ensemble
		foreach ($rev_domain as $key => $part){
			$match = ($part == $rev_project_ensemble[$key]);
			if(!$match) break;
		}

		// If we are in a subdomain of the project ensemble, then remove the project ensemble from the domain
		// ie: if the project ensemble is 'system-00.suitespace.corp' and the host is 'api.system-00.suitespace.corp', 
		// then the label will be 'api'. For 'api.system-00.suitespace.corp', and ensemble suiteux.com, the
		// label will be 'api_system_00_suitespace_corp'

		if ($match){
			$domain = array_slice($domain, 0, count($domain) - count($rev_project_ensemble));
		}

		return static::adjust_generated_label( 		// Fine-tune the generated label
			implode(								// Handle hostnames before adjusting
				'_', 								// Replace . with _
				$domain								// Split host into array
			) 
		);
	}

	public static function adjust_generated_label($original):string
	{
		$adjusted = '';
		$original = str_replace(['-', '___', '__'], ['_', '_', '_'], $original);		
		for ($i = 0; $i < 10; $i++) {
			// PHP Class name constraints:
			// - Must start with a letter or underscore
			// - Can only contain letters, numbers, and underscores
			// - Cannot contain spaces, dashes, or other punctuation
			// - Cannot start with a number
			// - Cannot be a reserved word

			$current_char = $original[$i];
			$is_safe = ctype_alnum($current_char) || $current_char == '_';
			$is_alpha = ctype_alpha($current_char);
			$first_character_is_safe =
				$i == 0 ?
				($is_alpha || $current_char == '_')
				:		true;

			// If the current character is alphanumeric, or the first character is safe, then add it to $adjusted
			if ($is_safe && $first_character_is_safe) {
				$adjusted .= $current_char;
			} elseif ($is_safe && !$first_character_is_safe) {
				$adjusted .= '_' . $current_char;
			} else continue;
		}

		$reserved_words = [
			'and', 'or', 'xor', 'as', 'break', 'case', 'cfunction', 'class', 'continue', 'declare', 'const', 'default', 'do',
			'else', 'elseif', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'extends', 'for',
			'foreach', 'function', 'global', 'if', 'new', 'old_function', 'static', 'switch', 'use', 'var', 'while', 'array',
			'die', 'echo', 'empty', 'exit', 'eval', 'include', 'include_once', 'isset', 'list', 'require', 'require_once',
			'return', 'print', 'unset', '__file__', '__line__', '__function__', '__class__', '__method__', 'final',
			'php_user_filter', 'interface', 'implements', 'instanceof', 'public', 'private', 'protected', 'abstract',
			'clone', 'try', 'catch', 'throw', 'this', 'namespace', 'goto', 'instanceof', 'insteadof', 'trait', '__dir__',
			'__namespace__', '__halt_compiler', 'yield', 'finally', 'int', 'float', 'bool', 'string', 'true', 'false', 'null',
			'void', 'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent', 'bool',
			'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self',
			'parent', 'bool', 'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable',
			'iterable', 'self', 'parent', 'bool', 'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed',
			'numeric', 'callable', 'iterable', 'self', 'parent', 'bool', 'false', 'null', 'void', 'iterable', 'object',
			'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent', 'bool', 'false', 'null', 'void',
			'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent', 'bool',
			'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable',
			'self', 'parent', 'bool', 'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed', 'numeric',
			'callable', 'iterable', 'self', 'parent', 'bool', 'false', 'null', 'void', 'iterable', 'object', 'resource',
			'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent', 'bool', 'false', 'null', 'void', 'iterable',
			'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent', 'bool', 'false', 'null',
			'void', 'iterable', 'object', 'resource', 'mixed', 'numeric', 'callable', 'iterable', 'self', 'parent',
			'bool', 'false', 'null', 'void', 'iterable', 'object', 'resource', 'mixed', 'numeric',

		];

		// Check if $adjusted is a reserved word
		$is_reserved = in_array($adjusted, $reserved_words);
		if ($is_reserved) {
			// If $adjusted is a reserved word, then prepend the project name to it
			$adjusted .= Scope::$Active->project . '_' . $adjusted;
		}
		return $adjusted;
	}

	public function connect( $host = null, $user = null, $pass = null, $db = null, $port = null, $socket = null ){

		$this->connection = new Connector(input: [$this->scope]);
		$state = $this->connection->connect();

		// If $state was an error, output the error at connector->connection
		if ( !($state instanceof nullstate) ){
			throw new \Exception('Connection failed: ' . $state);
		}
		elseif ( $state !== nullstate::defined ){
			switch ($state){
				case nullstate::undefined:
					throw new \Exception('The connection state was undefined.');
					break;
				case nullstate::undeclared:
					throw new \Exception('The connection state was undeclared.');
					break;
				case nullstate::ambiguous:
					throw new \Exception('The connection state was ambiguous.');
					break;
				case nullstate::null:
					throw new \Exception('The connection state was null.');
					break;
				default:
					throw new \Exception('The connection state was vey ambiguous.');
					break;
			}
		}
		elseif ( $state === nullstate::defined ){
			$this->is_connected = true;
			// $this->connection = $this->connector->connection;
		}
		else{
			throw new \Exception('The connection state was very ambiguous.');
		}

		return $state;
	}

	/**
	 * Check if we are in a pooled cluster or not
	 * 
	 * @return bool	True if we are in a pooled cluster, false if not
	 */

	public function check_pooled(){
		// Feel free to override this function in your own Spooler class
		// Approach is designed to be used in a pooled cluster, and connection sharing wont hurt anything
		return true;
	}

    public function createPool($configs)
    {
		
		foreach ($configs as $config)
        {
			$Spooler = new self(...$config);
			$Spooler->connect(...$config);
			$proto = $Spooler->connector->getProtocol();

			if( empty(Service::$protocols[$proto][$Spooler->label])){
				if( $this->label == $Spooler->label ){
					Service::$protocols[$proto][$this->label] = $this;
					$this->pool[] = $Spooler;
				}
			}
			else
				Service::$protocols[$proto][$this->label]->pool[] = $Spooler;
        }
        return self::$pool;
    }

    public static function getPool($label){
		if( empty(Service::$protocols['CoolSpool']) ){
			return [];
		}
		if( $label == '' || $label == '*'){
			return Service::$protocols['CoolSpool'][$label] ?? Service::$protocols['CoolSpool'] ?? [];
		}
		return Service::$protocols['CoolSpool'][$label] ?? [];
    }

    public static function getPoolCount($label)
    {
        return count((self::getPool($label)?->pool ?? [])) + 1;
    }

    public static function getPoolConnection($label)
    {
        $pool = self::getPool($label);

        // If there is only one connection in the pool, return it
        return count($pool) > 0 ?
            $pool[0] :
            $pool[rand(0, count($pool) - 1)];
    }


	// Load data using associative fetch
	// public function load($query, $mysqli, $TypeCatalog){

	// }

	/**
	 * 	Discover the Spooler
	 * - Query a list of all Catalogs
	 * - Use Scope::GetPath(path::resource) to get the project's resource class root
	 * - Generate class file for resource_root/ThisLabel.php  (class extends Approach\Resource\CoolSpool\Spooler)
	 * - Generate class file for resource_root/ThisLabel/[each db name].php  (class extends Approach\Resource\CoolSpool\Catalog)
	 * - Call each Catalog's discover() method
	 */

	/**
	 * 	Get a list of TypeCatalogs
	 * @param string $catalog The Catalog name to list TypeCatalogs from
	 * @return array A list of TypeCatalogs
	 */

	public function GetTypeCatalogList($catalog)
	{
		//escape input
		$catalog = $this->connection->real_escape_string($catalog);

		//query for TypeCatalog names
		$result = $this->connection->query('SHOW TypeCatalogS IN ' . $catalog);
		$TypeCatalogs = [];
		while ($row = $result->fetch_assoc())
		{
			$TypeCatalogs[] = $row['TypeCatalogs_in_' . $catalog];
		}
		return $TypeCatalogs;
	}

	/**
	 * 	Get a list of Catalogs
	 */

	public function GetCatalogList()
	{
		// Check if URL has any parameters
		$url_params = [];
		$param_seperator = '?';
		if (strpos($this->scope, '?') !== false){
			// If there were already parameters, then we need to use & instead of ? later
			$param_seperator = '&';	
			
			/*** /// Erase the space to parse the URL parameters

			$url_params = explode('?', $this->scope)[1];
			$temp = explode('&', $url_params);
			$url_params = [];

			foreach ($temp as $param){
				$param = explode('=', $param);
				$url_params[$param[0]] = $param[1];
			}
			/***/
		}


		$url = $this->scope . $param_seperator.'access_mode=PUBLISH_PROFILE';
		// $uri = $url . urlencode(json_encode($json));

		$fetcher = new Service(
			target_in: target::transfer, 
			target_out: target::variable,
			input: $url,
			auto_dispatch: true
		);

		$profile = $fetcher->payload;

		return $profile;
	}

	/**
	 * Get a list of fields in a TypeCatalog
	 * @param string $catalog The Catalog name to list fields from
	 * @param string $TypeCatalog The TypeCatalog name to list fields from
	 * @return array A list of fields
	 */

	public function GetfieldList($catalog, $TypeCatalog){
		//escape input
		$catalog = $this->connection->real_escape_string($catalog);
		$TypeCatalog = $this->connection->real_escape_string($TypeCatalog);

		//query for field names
		$result = $this->connection->query('SHOW fieldS FROM ' . $catalog . '.' . $TypeCatalog);
		$fields = [];
		while ($row = $result->fetch_assoc())
		{
			$fields[] = $row['Field'];
		}
		return $fields;
	}

	/**
	 * Get a list of accessors in a TypeCatalog
	 * - Primary Accessors are generally the primary key
	 * - Reference Accessors are unique indexes, foreign keys, etc
	 * - Accessors may be used to locate associated records or perform joins
	 * - May be multi-field
	 * @param string $catalog The Catalog name to list accessors from
	 * @param string $TypeCatalog The TypeCatalog name to list accessors from
	 * @return array A list of accessors
	 */
	public function GetAccessorList($catalog, $TypeCatalog){
		//escape input
		$catalog = $this->connection->real_escape_string($catalog);
		$TypeCatalog = $this->connection->real_escape_string($TypeCatalog);
		$accessors = [
		];

		// Primary Accessor
		$result = $this->connection->query('SHOW INDEX FROM ' . $catalog . '.' . $TypeCatalog . ' WHERE Key_name = \'PRIMARY\'');

		while ($row = $result->fetch_assoc()){
			$accessors['primary'][] = $row['field_name'];
		}

		// Unique Accessors
		$result = $this->connection->query('SHOW INDEX FROM ' . $catalog . '.' . $TypeCatalog . ' WHERE Non_unique = 0');

		while ($row = $result->fetch_assoc()){
			$accessors['unique'][] = $row['field_name'];
		}

		// Foreign Key Accessors

		// $result = $this->connection->query('SELECT * FROM information_schema.KEY_field_USAGE WHERE TypeCatalog_SCHEMA = \''.$catalog.'\' AND TypeCatalog_NAME = \''.$TypeCatalog.'\' AND REFERENCED_TypeCatalog_NAME IS NOT NULL;');
		$result = $this->connection->query('SELECT * FROM information_schema.KEY_field_USAGE WHERE TypeCatalog_SCHEMA = \''.$catalog.'\' AND TypeCatalog_NAME = \''.$TypeCatalog.'\' AND REFERENCED_TypeCatalog_NAME IS NOT NULL;');
		while ($row = $result->fetch_assoc()){
			$accessors['foreign'][$row['REFERENCED_TypeCatalog_NAME']][] = $row['field_NAME'];
		}
		
		return $accessors;
	}



	/**
	 * 	Discover the Catalog
	 */
	public function discover(){

		$resource_root = Scope::GetPath(path::resource).'CoolSpool/';
		$resource_ns = Scope::$Active->project . '\\Resource\\CoolSpool\\';
		$safe =''; // We will use this to hold a safe version of $this->label
		
		// Check if $this->label starts with 'p:' (persistent)
		// If so, then remove it and set that result to $safe
		$safe = substr($this->label, 0, 2) == 'p:' ?
		substr($this->label, 2) :
		$safe = $this->label;
		
		// Remove characters that are invalid for class names for this->label
		$safe = preg_replace('/[^a-zA-Z0-9_]/', '', $safe);
		
		/* TODO: Actually use Imprint::Mint with a Class Pattern */
		$this->MintResourceClass(
			path: $resource_root.$safe.'.php',
			class: Scope::$Active->project.'\\Resource\\CoolSpool\\'. $safe,
			extends: static::class,
			namespace: Scope::$Active->project.'\\Resource\\CoolSpool\\',
			uses: [],
			// constants: [],
			// properties: [],
			// methods: [],
		);

		// Discover the Catalogs
		$dbs = $this->GetCatalogList();
		foreach ($dbs as $db){
			$catalog = new TypeCatalog($this, $db);
			$catalog->discover();
		}
	}
	
	/**
	 * Mint a class file for a Catalog
	 * @param string $path The path to write the class file to
	 * @param string $class The class name
	 * @param string $extends The name of the class to extend
	 * @param string $namespace The namespace to unselectOption('selector');
	 * @param string[] $uses A list of classes to use
	 * @param string[] $constants A list of constants to define
	 * @param string[] $properties A list of properties to define
	 * @param string[] $methods A list of methods to define
	 */
	public function MintResourceClass(
		string $path,
		string $class,
		string $extends,
		string $namespace,
		array $uses = [],
		array $constants = [],
		array $properties = [],
		array $methods = [],
		$overwrite = false
	): void{
		// Grab the last part of the class name for the label
		$class = explode('\\', $class);
		$class = $class[count($class) - 1];

		$extends = $extends ?? '\Approach\Resource\CoolSpool\Spooler';
		$namespace = $namespace ?? Scope::$Active->project.'\Resource';
		$uses = $uses ?? [
			static::class,
		];
		if(empty($constants)){
			if (!empty($this->scope))
				$constants[] = 'const SCOPE = \'' . $this->scope . '\';';
			if(!empty($this->host)) 
				$constants[]= 'const HOST = \''.$this->host.'\';';
			if(!empty($this->user)) 
				$constants[]= 'const USER = \''.$this->user.'\';';
			// if(!empty($this->pass)) 
			// 	$constants[]= 'const PASS = \''.$this->pass.'\';';
			if(!empty($this->Catalog)) 
				$constants[]= 'const Catalog = \''.$this->Catalog.'\';';
			if(!empty($this->port)) 
				$constants[]= 'const PORT = \''.$this->port.'\';';
			if(!empty($this->socket)) 
				$constants[]= 'const SOCKET = \''.$this->socket.'\';';
			// if(!empty($this->ssl_key)) 
			// 	$constants[]= 'const SSL_KEY = \''.$this->ssl_key.'\';';
			if(!empty($this->ssl_cert)) 
				$constants[]= 'const SSL_CERT = \''.$this->ssl_cert.'\';';
			if(!empty($this->ssl_ca)) 
				$constants[]= 'const SSL_CA = \''.$this->ssl_ca.'\';';
			if(!empty($this->ssl_capath)) 
				$constants[]= 'const SSL_CAPATH = \''.$this->ssl_capath.'\';';
			if(!empty($this->ssl_cipher)) 
				$constants[]= 'const SSL_CIPHER = \''.$this->ssl_cipher.'\';';
			if(!empty($this->charset)) 
				$constants[]= 'const CHARSET = \''.$this->charset.'\';';
			if(!empty($this->collation)) 
				$constants[]= 'const COLLATION = \''.$this->collation.'\';';
			if(!empty($this->timeout)) 
				$constants[]= 'const TIMEOUT = \''.$this->timeout.'\';';
			if(!empty($this->persistent)) 
				$constants[]= 'const PERSISTENT = \''.$this->persistent.'\';';
			if(!empty($this->skip_connection)) 
				$constants[]= 'const SKIP_CONNECTION = \''.$this->skip_connection.'\';';
			if(!empty($this->is_pooled)) 
				$constants[]= 'const IS_POOLED = \''.$this->is_pooled.'\';';
			$constants[] = 'const CONNECTOR_CLASS = \'\\Approach\\Service\\CoolSpool\\Connector\';';
		}

		$traits = $traits ?? [
			// Add additional trait blocks for your Spooler classes here
			// 'use \Approach\Resource\MyResource\connectivity;',
			// 'use \Approach\Resource\MyResource\discovery;',...
			'use '.$class.'_user_trait;',
		];

		$properties = $properties ?? [ 
			// Add additional properties for your Spooler classes here
			//'public bool $is_connected = false;',
		];

		$methods = $methods ?? [
			// Add additional method blocks for your Spooler classes here
		];
		$insert=[];
		$insert[] = implode(PHP_EOL."\t", $traits);
		$insert[] = implode(PHP_EOL."\t", $constants);
		$insert[] = implode(PHP_EOL."\t", $properties);
		$insert[] = implode(PHP_EOL."\t", $methods);

		$namespace = trim($namespace, '\\');
		$extends = '\\'.trim($extends, '\\');
		// Generate the class file
		$content = '<?php'.PHP_EOL.PHP_EOL.<<<CLASS
namespace $namespace;

class $class extends $extends
{
	{$insert[0]}
	{$insert[1]}
	{$insert[2]}
	{$insert[3]}
}

CLASS;

		$file_dir = dirname($path);
		// Make sure the path/ and path/user_trait.php exist
		if (!file_exists($file_dir)) mkdir($file_dir, 0660, true);
		if (!file_exists($file_dir . '/' . $class . '_user_trait.php')) {
			$user_trait =
			'<?php

namespace ' . $namespace . ';

trait ' . $class . '_user_trait
{
	/**** User Trait ****
	 * 
	 *  This trait is used to add user functionality to an Approach Resource.
	 * 
	 *  Anything you add here will be available to the primary resource of
	 *  this namespace. 
	 * 
	 *  This is a good place to use hooks and/or override methods to achieve
	 *  desired functionality.
	 * 
	 *  Examples include: 
	 *    - Adding a user_id property
	 *    - Changing the behavior of the load() or save() method
	 *    - Adding behavior with preload(), onsave(), postpush(), onpull(), preacquire(), etc..
	 *    - Adding functions that work with your custom operations and aspects
	 *    - Tieing into the map system deeper
	 * 
	 * This trait is automatically included in the class that is generated, so
	 * you can use it immediately. This file is here for your convenience
	 * and will not be overwritten by the generator.
	 * 
	 */
}';

			$file = fopen($file_dir . '/' . $class . '_user_trait.php', 'w');
			fwrite($file, $user_trait);
			fclose($file);
		}

		$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

		// swap out backslashes for forward slashes on windows
		$path = $isWindows ? str_replace('\\', '/', $path) : $path;

		// Create the directory if it doesn't exist
		$dir = dirname($path);
		if (!is_dir( $dir )){
			mkdir($dir, 0660, true);
		}

		echo PHP_EOL.'Creating class file: '.$path.PHP_EOL;
		// Write the class file
		file_put_contents($path, $content);
	}
}