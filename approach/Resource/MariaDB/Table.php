<?php

namespace Approach\Resource\MariaDB;

use \Approach\Resource\Resource;
use \Approach\Resource\MariaDB\Aspect\Table as discovery;
use \Approach\Resource\Aspect\Aspect;
use Approach\Resource\MariaDB\Table\sourceability;
use Approach\Resource\sourceability as root_sourceability;

/**
 * MariaDB Table resource class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @subpackage	MariaDB
 * @version		2.0.0
 * @category	Resource
 * @see			https://approach.orchestrationsyndicate.com/docs/2.0/resource/mariadb/table
 * 
 */

// Allow table to use dynamic property names
// Since this was deprecated without an attribute we will add the attribute here

class Table extends Resource
{
	public $database_class = '\\Approach\\Resource\\MariaDB\\Database';
	public $database_name = 'MyHome';
	public $server_class = '\\Approach\\Resource\\MariaDB\\Server';
	public $server_name = 'data.my.home';
	public $connector_class = '\\Approach\\Service\\MariaDB\\Connector';
	public $resource_proto = 'MariaDB';

	// use discovery;
	use root_sourceability, sourceability{
		sourceability::push insteadof root_sourceability;
	}

	public function __construct(public $name = 'resources', public $database=null)
	{
		// Get the class constants and set them as properties if they are not already set
		foreach (get_class_vars(static::class) as $key => $value) {
			if (!isset($this->{$key}) && defined(static::class . '::' . $key)) {
				$this->{$key} = constant(static::class . '::' . $key);
			}
		}

		// Get an instance of the database
		if ($database instanceof \Approach\Resource\MariaDB\Database) {
			$this->database = $database;
		} 
		else {
			try{
				$this->database = new $this->database_class;
			}
			catch (\Throwable $e) {
				try {
					$this->database = Resource::find('MariaDB://' . $this->server_name . '/' . $this->database_name );
				}
				catch (\Throwable $e) {
					throw new \Exception('Unable to create database instance');
				}
			}
		}
	}

	public function discover()
	{
		// echo PHP_EOL.$this->name. ' is discovering'.PHP_EOL;
		discovery::define( caller: $this, which: discovery::field );
	}

	public static function get_database()
	{
		// Current self::class will be some_project\src\Resource\MyDb_Host_corp\MyDB\MyTable
		// Service::$protocols[$proto][$server->alias]->connection holds the database connection as a Resource\MariaDB\Server->connection

		// Get the database name from the self::class
		// Get the database connection from the Service::$protocols['MariaDB'][ $hostname ][ $database_name ]
		// ['MariaDB'] is the Service\MariaDB\Connector
		// [$hostname] is the Resource\MariaDB\Server
		// [$database_name] is the Resource\MariaDB\Database

		$namespace_components = explode('\\', self::class);

		// We don't really know how nested we are, but the server is always 2 up from the table and the database is 1 up
		$database_name = $namespace_components[count($namespace_components) - 2];
		$server_name = $namespace_components[count($namespace_components) - 3];
	}


	/**
	 * Mint a resource class file
	 *
	 * TODO: Use Imprint & Patterns instead
	 *	  - Requires Loop node
	 *	  - Even better if made as Components in a Composition
	 *	  - When both are done
	 *		  - make these arguments into a new Render\Node format
	 *		  - add Decoder and Encoder for Services to exchange  Render\ClassMetadata with Resource\Type
	 */
	public static function MintResourceClass2(
		string $path,
		string $class,
		string $extends,
		string $namespace,
		array $uses = [],
		array $constants = [],
		array $properties = [],
		array $methods = [],
		$overwrite = false
	): void {
		// If the file does not exist, then build it
		if (!file_exists($path) || $overwrite) {
			// Grab the last part of the class name for the label
			$class = explode('\\', $class);
			$class = $class[count($class) - 1];

			$extends = $extends ?? '\Approach\Resource\MariaDB\Server';
			$namespace =
				$namespace ?? \Approach\Scope::$Active->project . '\Resource';
			$uses = $uses ?? [static::class];

			$content = '<?php ' . PHP_EOL . PHP_EOL;

			// Write the namespace
			$content .= 'namespace ' . $namespace . ';' . PHP_EOL . PHP_EOL;

			foreach ($uses as $use) {
				$content .= 'use ' . $use . ';' . PHP_EOL;
			}
			$content .= 'use ' . $namespace . '\\Aspect\\' . $class . '\\user_trait as aspects;' . PHP_EOL . PHP_EOL;
			$profilePath = $namespace;
			// make it into \Resource\Aspect\MariaDB
			$profilePath = str_replace(
				'\\Resource\\MariaDB',
				'\\Resource\\MariaDB\\Aspect',
				$profilePath
			);

			// Write the class
			$content .=
				'class ' . $class . ' extends ' . $extends . '{' . PHP_EOL . PHP_EOL;

			$content .= "\t" .	'/** Link minted Resource to its Aspects Profile */' . PHP_EOL;
			$content .= "\t" . 'public static function GetProfile()		{ 	return aspects::$profile;	}' . PHP_EOL;
			$content .= "\t" . 'public static function GetSourceName()	{	return aspects::$source;	}' . PHP_EOL . PHP_EOL;

			$content .=
				"\t// Change the user_trait to add functionality to this generated class" .
				PHP_EOL;
			foreach ($constants as $constant) {
				$content .= "\t" . 'const ' . $constant . ';' . PHP_EOL;
			}
			foreach ($properties as $property) {
				$content .= "\t" . 'public ' . $property . ';' . PHP_EOL;
			}
			foreach ($methods as $method) {
				$content .= "\t" . $method . PHP_EOL;
			}
			$content .= '}' . PHP_EOL;

			$file_dir = dirname($path);
			$profileFileDir = str_replace(
				'Resource/MariaDB',
				'Resource/MariaDB/Aspect',
				$file_dir
			);
			$profileFileDir .= '/' . $class;

			//			$namespacePath = $profilePath . '\\'.$class;

			// Make sure the path/ and path/user_trait.php exist
			if (!file_exists($file_dir)) {
				mkdir($file_dir, 0770, true);
			}
			if (!file_exists($profileFileDir . '/' . 'user_trait.php')) {
				$user_trait =
					'<?php

namespace ' .
					$profilePath .
					'\\' .
					$class .
					';

trait user_trait
{
	use profile;
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
	 *	- Changing the behavior of the load() or save()
	 *	- Adding behavior with preload(), onsave(), postpush(), onpull(), preacquire(), etc..
	 *	- Adding functions that work with your custom operations and aspects
	 *	- Tieing into the map system deeper
	 *
	 * This trait is automatically included in the class that is generated, so
	 * you can use it immediately. This file is here for your convenience
	 * and will not be overwritten by the generator.
	 *
	 */
}';
				if (!is_dir($profileFileDir)) {
					mkdir($profileFileDir, 0777, true);
				}
				$file = fopen($profileFileDir . '/' . 'user_trait.php', 'w');
				fwrite($file, $user_trait);
				fclose($file);
			}

			$file = fopen($path, 'w');
			fwrite($file, $content);
			fclose($file);
		}
	}

 }
