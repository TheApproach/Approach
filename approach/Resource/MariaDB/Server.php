<?php

/**
 * The Approach Resource MariaDB Server class
 * ===============================
 * This class is used to define a MariaDB Server resource which can be used to connect to a MariaDB Server.
 * Coordinating with Approach\Service\MariaDB\Connector and Approach\Resource\MariaDB\Database, this class can be used to
 * discover a MariaDB Server and its databases and tables, as well as the columns and accessors (primary key, unique
 * indexes, foreign keys, etc) for those tables.
 *
 * Resources and Service Connectors can be used to build a database abstraction layer which can be used to generate
 * queries. Services register a path-style protocol such as 'MariaDB' or 'SQL' and then Resources can be used to
 * connect to a MariaDB Server or SQL Server. Once connected, the Resources can be used to discover the databases,
 * tables, columns, and accessors for those columns. Using the discovered information, the Service Connectors can
 * build queries to retrieve, insert, update, and delete data.
 *
 * // Selects all records from the table with the name 'MyTable' in the database with the name 'MyDatabase'
 * MariaDB://MyServer/MyDatabase/MyTable
 *
 * // Selects the id, type, age, and name columns from the table 'MyTable' in database 'MyDatabase'
 * // Filters the results to where id is between 0 and 1000, type is 3, and age is between 0 and 20
 * MariaDB://MyServer/MyDatabase/MyTable[id, type, age, name][id: 0..1000, type: 3, age: 0..20]
 *
 * // Selects the id, type, age, and name columns from the table 'MyTable' in database 'MyDatabase'
 * // Filters the results to where id is between 0 and 1000, type is 3, and age is between 0 and 20
 * // Orders the results by age ascending, then by name descending. The order by clause is optional.
 * // Limits the results to 10 records. The limit clause is optional.
 * ../MyTable[id, type, age, name][id: 0..1000, type: 3, age: 0..20]; order: age asc, name desc; limit: 10
 *
 * // Selects the id, other, age, and name columns from the table 'MyTable' in database 'MyDatabase'
 * // Filters the results to where id is between 0 and 1000
 * // Limits the results to 100 records. The limit clause is optional.
 * // Joins the table 'Others' in database 'MyDatabase' on the column 'id' in 'MyTable' and 'id' in 'Others'
 * // Filters the results to where 'Others'.'id' is between 0 and 1000
 * ../MyTable[id, other, age, name][id: 0..1000]; limit: 100; join: Others[id][id: 0..1000] on MyTable.id = Others.id
 *
 * // Selects the id, type, and name columns from the table 'MyTable' in database 'MyDatabase'
 * // Searches for the string 'hello world' in the column 'name'
 * // Using the known "reference accessor" @other which the underlying system knows can load a class Other using the
 * // value of the column 'other' as the 'id' of the Other class; we then infer this
 * ../MyTable[id, type, name][name: 'hello world'].other/Others[id, other][id: MyTable.other]
 *
 * // Selecting an arbitrary set of results, then creating a new set of results from that set of results in the next path component
 * ../MyTable[id, type, name][name: 'hello world']/Other@*[id: 0..1000].other
 *
 * This is a more complex example where we select a set of users who have the name 'John'.
 * For each such user:
 * 	- we select the set of posts they authored
 * 	- from those posts we select all comments
 * 	- from those comments we select the set of users who authored those comments
 *  - repeat the process for each of the commenters' posts' commenters
 *
 * ../User[name: 'John']/Post[author: User.id]/Comment[post: Post.id]
 * 		/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]
 * 			/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]
 * 				/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]
 * 					/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]
 * 						/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]
 * 							/User[id: Comment.author]/Post[author: User.id]/Comment[post: Post.id]...
 *
 * While this URL would be too long for most browsers to handle; you can stream results in/out while simultaneously
 * adding results to the stream. This allows you to create a single stream which returns a set of results without
 * having to wait for the entire set of results to be loaded into memory.
 *
 * Especially useful when coupled with limits and offsets, to stream the next block of results while others are being
 * processed. Loaded path-components can be removed from the URL to reduce the length of the URL while constantly
 * adding new path components to the end of the URL. This allows you to fork the URL into multiple paths, each of which can
 * be processed independently, and merged back into the same stream. This allows you to create a single stream which
 * returns a set of results without having to wait for the entire set of results to be loaded into memory.
 *
 * Though this is a complex example, it is still a simple query. Only the last path component is a query. The rest of
 * the path components are used to select the set of data to query. The query is then run on each set of data selected
 * from the previous path component.
 *
 * Most filesystems, browsers, and other tools, we are familiar with the idea of a file system path. The Approach
 * Framework extends this idea to resources and services. The Approach Framework uses a path-style protocol to identify
 * a resource or service, and then uses path components to locate the data to query or manipulate.
 *
 * While the above examples are for a MariaDB Server, the same query language can be used for any type of database.
 * For example, you could use the same query language to query a MongoDB, Redis, or Memcached server. The query
 * language is not specific to any particular database technology. The Approach Framework extends the idea of a
 * filesystem path to any resource or service.
 *
 *
 *
 *
 *
 * MariaDB://MyServer/MyDatabase/MyTable[@primary_accessor]
 *
 *
 * @package		Approach
 * @subpackage	Resource
 * @subpackage	MariaDB
 * @category	MariaDB Server
 *
 */

namespace Approach\Resource\MariaDB;

use Approach\Service\Service;
use Approach\Service\MariaDB;
use Approach\Render\Node;
use Approach\Resource\Resource;
use Approach\Resource\MariaDB\Aspect\Server as server_discoverable;
use Approach\Resource\MariaDB\Aspect\Database as discoverable;
use \stringable;
use \Approach\Scope;
use \Approach\path;
use \Approach\deploy;
use \Approach\nullstate;
use Approach\Resource\Aspect\Aspect;
use Approach\Resource\MariaDB\Aspect\quality;
use Approach\Resource\MariaDB\Aspect\profile;
use Approach\runtime;
use ReflectionClass;

// Discoverable Qualities
const HOST_INDEX = 0;
const USER_INDEX = 1;
const PASS_INDEX = 2;
const DATABASE_INDEX = 3;
const PORT_INDEX = 4;
const SOCKET_INDEX = 5;
const PERSISTENT_INDEX = 6;
	
class Server extends Resource
{
	protected $pool = [];
	protected static $configs = [];
	protected bool $is_connected = false;
	protected bool $has_persistent = false;
	public $connection;



	public function __construct(

		// Normal connection details
		public null|string|stringable|Node $host    = null,
		public null|string|stringable|Node $user    = null,
		public null|string|stringable|Node $pass    = null,
		public null|string|stringable|Node $database = null,
		public ?int $port     = null,
		public null|string|stringable|Node $socket    = null,

		// SSL connection details
		public null|string|stringable|Node $ssl_key    = null,
		public null|string|stringable|Node $ssl_cert    = null,
		public null|string|stringable|Node $ssl_ca        = null,
		public null|string|stringable|Node $ssl_capath    = null,
		public null|string|stringable|Node $ssl_cipher    = null,

		// Connection options
		// public null|string|stringable|Node $client_flags    = null,        // Ex: MYSQLI_CLIENT_SSL, MYSQLI_CLIENT_COMPRESS, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
		public null|string|stringable|Node $charset        = null,        // Ex: utf8mb4
		public null|string|stringable|Node $collation        = null,        // Ex: utf8mb4_unicode_ci
		public ?int $timeout        = null,        // Ex: 5

		// Connection pool options
		public ?bool $persistent        = true,
		public ?bool $skip_connection    = false,
		protected ?bool $is_galera        = null,
		public null|string|stringable|Node $label    = null,
		bool $skip_profile = false
	) {
		$p = $this->persistent ? 'p:' : '';

		// // Check for any class constants which can be used as defaults
		// foreach (get_class_vars(static::class) as $key => $value) {
		// 	if (!isset($this->{$key}) && defined(static::class . '::' . $key)) {
		// 		$this->{$key} = constant(static::class . '::' . $key);
		// 	}
        // }
		
		$profile = $skip_profile ? [] : static::GetProfile();

		// if(empty($this->pass)){
		// 	 $this->pass = null;
		// }
		// Fallback Defaults
		$this->host        = $this->host     ??	$profile[Aspect::quality][HOST_INDEX][quality::state] 	?? Scope::GetDeploy(deploy::resource);
		$this->user        = $this->user     ??	$profile[Aspect::quality][USER_INDEX][quality::state] 	?? Scope::GetDeploy(deploy::resource_user);
		$this->pass        = $this->pass     ??	$profile[Aspect::quality][PASS_INDEX][quality::state] 	?? ini_get('mysqli.default_pw');
		$this->port        = $this->port     ??	$profile[Aspect::quality][PORT_INDEX][quality::state] 	?? ini_get('mysqli.default_port') ?? 3306;
		$this->socket      = $this->socket   ??	$profile[Aspect::quality][SOCKET_INDEX][quality::state] 	?? ini_get('mysqli.default_socket') ?? '/var/lib/mysql/mysql.sock';

		$this->label       = $p . $this->label ?? $p . $this->get_fallback_label();
		$this->set_render_id();

		// Check if any SSL options are set; throw an error if unable to connect
		$this->use_ssl =
			$this->ssl_key ||
			$this->ssl_cert ||
			$this->ssl_ca ||
			$this->ssl_capath ||
			$this->ssl_cipher;

		/* Check if we should skip this connection or not */
		if (!$this->skip_connection) {
			$this->connect(
				$this->host,
				$this->user,
				$this->pass,
				$database,
				$this->port,
				$this->socket
			);
			// Check if we are in a galera cluster or not
			if ($this->is_galera === null) {
				$this->is_galera = $this->check_galera();
			}
		}
	}

	public function get_fallback_label()
	{
		// normalize host to safe characters for class name
		$domain = explode('.', $this->host);
		$is_ip = false;
		foreach ($domain as $part) {
			if (is_numeric($part)) {
				$is_ip = true;
			} else {
				$is_ip = false;
				break;
			}
		}

		if (!$is_ip) {
			$rev_domain = array_reverse($domain);
			$rev_project_ensemble = array_reverse(
				explode(
					'.',
					Scope::GetDeploy(deploy::ensemble)
				)
			);

			$match = false;
			// Check if we are in a subdomain of the project ensemble
			foreach ($rev_domain as $key => $part) {
				$match = ($part == $rev_project_ensemble[$key]);
				if (!$match) break;
			}

			// If we are in a subdomain of the project ensemble, then remove the project ensemble from the domain
			if ($match) {
				$domain = array_slice($domain, 0, count($domain) - count($rev_project_ensemble));
			}

			$fallback = implode('_', $domain);
			// Replace any dashes with _ and remove character patterns which are safe in URL hostnames but not safe in PHP classnames
			$fallback = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace('-', '_', $fallback));

			// If the first character is a number, then prepend an underscore
			$fallback = is_numeric(substr($fallback, 0, 1)) ? '_' . $fallback : $fallback;
		} else $fallback = 'ip_' . implode('_', $domain);

		// $fallback = $is_ip ? 'ip' . implode('_', $domain) : $domain[0];
		return $fallback;
	}

	public function connect($host = null, $user = null, $pass = null, $db = null, $port = null, $socket = null)
	{
		/**
		 * if this exists dont connect use it and return nullstate::defined
		 * 
		 * $proto = $this->connector::getProtocol(); 
		 * Service::$protocols[ $proto ][ $this-> alias ]    
		 */
		
		// TODO: Make this MariaDB\Connector::getConnectionPool( $alias )
		
		$proto = MariaDB\Connector::getProtocol(); 
		if(isset(Service::$protocols[ $proto ][ $this-> alias ])){
			$this->connector = Service::$protocols[$proto][$this->alias];
			return nullstate::defined;
		} else if(isset(Service::$protocols[ $proto ][ $this->_render_id ])){
			$this->connector = Service::$protocols[$proto][$this->_render_id];
			return nullstate::defined;
		} else {

			$this->connector = new MariaDB\Connector();

			if(isset($pass)){
				$this->pass = $pass;
			}


			$state = $this->connector->connect(server: $this);
			// If $state was a MySQLi error number, then output the error from the MySQLi connection at connector->connection
			if (!($state instanceof nullstate) && $state > 0) {
				throw new \Exception('Connection failed: ' . $this->connection->connect_error);
			} elseif ($state instanceof nullstate && $state !== nullstate::defined) {
				switch ($state) {
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
			} elseif ($state instanceof nullstate && $state === nullstate::defined) {
				$this->is_connected = $this->connector->connected;
				$this->connection = $this->connector->connection;
			} else {
				throw new \Exception('The connection state was vey ambiguous.');
			}

			return $state;
		}
	}

	/**
	 * Check if we are in a galera cluster or not
	 *
	 * @return bool	True if we are in a galera cluster, false if not
	 */

	public function check_galera()
	{
		// Check if we are in a galera or not using MySQLi in $this->connection
		$result = $this->connection->query('SHOW GLOBAL STATUS LIKE \'wsrep_on\'');
		$row = $result->fetch_assoc();

		// Detect query errors
		if ($this->connection->errno) {
			throw new \Exception('MySQLi error: ' . $this->connection->error);
		}

		// If there was a result, then wsrep_on is ON, which means we are in a galera cluster
		if (!empty($row) && isset($row['Value'])) {
			return true;
		}

		return false;
	}

	public function createPool($configs)
	{

		foreach ($configs as $config) {
			$server = new self(...$config);
			$server->connect(...$config);
			$proto = $server->connector->getProtocol();

			if (empty(Service::$protocols[$proto][$server->label])) {
				if ($this->label == $server->label) {
					Service::$protocols[$proto][$this->label] = $this;
					$this->pool[] = $server;
				}
			} else
				Service::$protocols[$proto][$this->label]->pool[] = $server;
		}
		return self::$pool;
	}

	public static function getPool($label)
	{
		if (empty(Service::$protocols['MariaDB'])) {
			return [];
		}
		if ($label == '' || $label == '*') {
			return Service::$protocols['MariaDB'][$label] ?? Service::$protocols['MariaDB'] ?? [];
		}
		return Service::$protocols['MariaDB'][$label] ?? [];
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
	// public function load($query, $mysqli, $table){

	// }

	/**
	 * 	Discover the server
	 * - Query a list of all databases
	 * - Use Scope::GetPath(path::resource) to get the project's resource class root
	 * - Generate class file for resource_root/ThisLabel.php  (class extends Approach\Resource\MariaDB\Server)
	 * - Generate class file for resource_root/ThisLabel/[each db name].php  (class extends Approach\Resource\MariaDB\Database)
	 * - Call each database's discover() method
	 */

	/**
	 * 	Get a list of tables
	 * @param string $database The database name to list tables from
	 * @return array A list of tables
	 */

	public function GetTableList($database)
	{
		//escape input
		$database = $this->connection->real_escape_string($database);

		//query for table names
		$result = $this->connection->query('SHOW TABLES IN ' . $database);
		$tables = [];
		while ($row = $result->fetch_assoc()) {
			$tables[] = $row['Tables_in_' . $database];
		}
		return $tables;
	}

	/**
	 * 	Get a list of databases
	 */

	public function GetDatabaseList()
	{
		$result = $this->connection->query('SHOW DATABASES');
		$dbs = [];
		while ($row = $result->fetch_assoc()) {
			$dbs[] = $row['Database'];
		}
		return $dbs;
	}

	/**
	 * 	Get a list of columns in a table
	 * @param string $database The database name to list columns from
	 * @param string $table The table name to list columns from
	 * @return array A list of columns
	 */

	public function GetColumnList($database, $table)
	{
		//escape input
		$database = $this->connection->real_escape_string($database);
		$table = $this->connection->real_escape_string($table);

		//query for column names
		$result = $this->connection->query('SHOW COLUMNS FROM ' . $database . '.' . $table);
		$columns = [];
		while ($row = $result->fetch_assoc()) {
			$columns[] = $row['Field'];
		}
		return $columns;
	}

	/**
	 * Get a list of accessors in a table
	 * - Primary Accessors are generally the primary key
	 * - Reference Accessors are unique indexes, foreign keys, etc
	 * - Accessors may be used to locate associated records or perform joins
	 * - May be multi-column
	 * @param string $database The database name to list accessors from
	 * @param string $table The table name to list accessors from
	 * @return array A list of accessors
	 */
	public function GetAccessorList($database, $table)
	{
		//escape input
		$database = $this->connection->real_escape_string($database);
		$table = $this->connection->real_escape_string($table);
		$accessors = [];

		// Primary Accessor
		$result = $this->connection->query('SHOW INDEX FROM ' . $database . '.' . $table . ' WHERE Key_name = \'PRIMARY\'');

		while ($row = $result->fetch_assoc()) {
			$accessors['primary'][] = $row['Column_name'];
		}

		// Unique Accessors
		$result = $this->connection->query('SHOW INDEX FROM ' . $database . '.' . $table . ' WHERE Non_unique = 0');

		while ($row = $result->fetch_assoc()) {
			$accessors['unique'][] = $row['Column_name'];
		}

		// Foreign Key Accessors

		// $result = $this->connection->query('SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = \''.$database.'\' AND TABLE_NAME = \''.$table.'\' AND REFERENCED_TABLE_NAME IS NOT NULL;');
		$result = $this->connection->query('SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = \'' . $database . '\' AND TABLE_NAME = \'' . $table . '\' AND REFERENCED_TABLE_NAME IS NOT NULL;');
		while ($row = $result->fetch_assoc()) {
			$accessors['foreign'][$row['REFERENCED_TABLE_NAME']][] = $row['COLUMN_NAME'];
		}

		return $accessors;
	}



	/**
	 * 	Discover the database
	 */
	public function discover()
	{

		$resource_root = Scope::GetPath(path::resource) . 'MariaDB' . DIRECTORY_SEPARATOR;
		$resource_ns = Scope::$Active->project . '\\Resource\\MariaDB';
		$safe = ''; // We will use this to hold a safe version of $this->label

		// Check if $this->label starts with 'p:' (persistent)
		// If so, then remove it and set that result to $safe
		$safe = substr($this->label, 0, 2) == 'p:' ?
			substr($this->label, 2) :
			$safe = $this->label;

		// Remove characters that are invalid for class names for this->label
		$safe = preg_replace('/[^a-zA-Z0-9_]/', '', $safe);

		$constants = [];
		if (!empty($this->host))
			$constants[] = 'HOST = \'' . $this->host . '\'';
		if (!empty($this->user))
			$constants[] = 'USER = \'' . $this->user . '\'';
		if(!empty($this->pass))
			$constants[]= 'PASS = \''.$this->pass.'\'';
		if (!empty($this->database))
			$constants[] = 'DATABASE = \'' . $this->database . '\'';
		if (!empty($this->port))
			$constants[] = 'PORT = \'' . $this->port . '\'';
		if (!empty($this->socket))
			$constants[] = 'SOCKET = \'' . $this->socket . '\'';
		if(!empty($this->ssl_key))
			$constants[]= 'SSL_KEY = \''.$this->ssl_key.'\'';
		if (!empty($this->ssl_cert))
			$constants[] = 'SSL_CERT = \'' . $this->ssl_cert . '\'';
		if (!empty($this->ssl_ca))
			$constants[] = 'SSL_CA = \'' . $this->ssl_ca . '\'';
		if (!empty($this->ssl_capath))
			$constants[] = 'SSL_CAPATH = \'' . $this->ssl_capath . '\'';
		if (!empty($this->ssl_cipher))
			$constants[] = 'SSL_CIPHER = \'' . $this->ssl_cipher . '\'';
		if (!empty($this->charset))
			$constants[] = 'CHARSET = \'' . $this->charset . '\'';
		if (!empty($this->collation))
			$constants[] = 'COLLATION = \'' . $this->collation . '\'';
		if (!empty($this->timeout))
			$constants[] = 'TIMEOUT = \'' . $this->timeout . '\'';
		if (!empty($this->persistent))
			$constants[] = 'PERSISTENT = \'' . $this->persistent . '\'';
		if (!empty($this->skip_connection))
			$constants[] = 'SKIP_CONNECTION = \'' . $this->skip_connection . '\'';
		if (!empty($this->is_galera))
			$constants[] = 'IS_GALERA = \'' . $this->is_galera . '\'';
		$constants[] = 'CONNECTOR_CLASS = \'\\Approach\\Service\\MariaDB\\Connector\'';

		/* TODO: Actually use Imprint::Mint with a Class Pattern */
		$classname = $resource_ns . '\\' . $safe ;
		// if (!class_exists($classname)) {
			$this->MintResourceClass(
				path: $resource_root . $safe . '.php',
				class: Scope::$Active->project . '\\Resource\\MariaDB\\' . $safe,
				extends: 'MariaDB\Server',
				namespace: Scope::$Active->project . '\\Resource\\MariaDB',
				uses: ['\Approach\Resource\MariaDB'],
				constants: $constants,
				// properties: [],
				// methods: [],
			);
		// }


		$this->__update_composer_autoloader(
			resource_root: NULL,
			resource_class: 'MariaDB\\' . $safe
		);

		// echo PHP_EOL . PHP_EOL;
		// exit('MariaDB\\' . $safe);
		$name = Scope::$Active->project . '\\Resource\\MariaDB\\' . $safe;
		// TO DO: figure out better symbol forwarding / looping mechanism
		$init = [
			'host'				=>	$this->host,
			'user'				=>	$this->user,
			'pass'				=>	$this->pass,
			'database'			=>	$this->database,
			'socket'			=>	$this->socket,
			'ssl_key'			=>	$this->ssl_key,
			'ssl_cert'			=>	$this->ssl_cert,
			'ssl_ca'			=>	$this->ssl_ca,
			'ssl_capath'		=>	$this->ssl_capath,
			'ssl_cipher'		=>	$this->ssl_cipher,
			'charset'			=>	$this->charset,
			'collation'			=>	$this->collation,
			'timeout'			=>	$this->timeout,
			'persistent'		=>	$this->persistent,
			'skip_connection'	=>	$this->skip_connection,
			'is_galera'			=>	$this->is_galera,
			'label'				=>	$this->label,
			'skip_profile'		=>	true
		];
		foreach($init as $k => $v){
			if( empty($v) ) unset($init[$k]);
		}
		
		$tmp = new $name(
			...$init
		);

		server_discoverable::define(caller: $tmp);



		// Discover the databases
		$dbs = $this->GetDatabaseList();
		foreach ($dbs as $db) {
			$database = new Database($this, $db);
			$database->discover();

			$safe_database_name = $database->sanitize_class_name($database->database);

			$resource_root = $resource_root ?? path::resource->get();
            $resource_ns = '\\' . Scope::$Active->project . '\\Resource';
            $name = $resource_ns . '\\MariaDB\\' . $safe . '\\' . $safe_database_name;

            $database->__update_composer_autoloader(
                resource_root: NULL,
                resource_class: 'MariaDB\\' . $safe . '\\' . $safe_database_name
            );

			$tmp = new $name(
				$this, $db
            );

			discoverable::define(caller: $tmp);

			/*static::define(caller: $database);*/
		}
	}
}
