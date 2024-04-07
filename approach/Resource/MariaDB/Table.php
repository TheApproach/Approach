<?php

namespace Approach\Resource\MariaDB;

use \Approach\Resource\Resource;
use \Approach\Resource\MariaDB\Aspect\Table as discovery;
use \Approach\Resource\Aspect\Aspect;

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
	
 }