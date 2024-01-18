<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Resource\discoverability as resource_discoverability;
use \Approach\Resource\Aspect\discover;
use Approach\Resource\Resource;

/**
 * discover		- defines the types of aspects Resource classes can have
 *				- defines the define_[aspect]() method for generating Aspect classes
 * 
 * @package		Approach\Resource
 * @subpackage	MariaDB
 * @version		2.0.-1
 * @category	Aspect
 * 
 */

trait database_discoverability
{
	public static function define_locations($caller)
	{
		if ($caller->server->connection instanceof \Approach\nullstate)
		{
			throw new \Exception('Server is not connected, nullstate::' . $caller->server->connection->name);
		}
		//escape input
		$database = $caller->server->connection->real_escape_string($caller->database);

		//query for table names
		$result = $caller->server->connection->query('SELECT * FROM information_schema.TABLES WHERE TABLE_TYPE = \'BASE TABLE\' AND TABLE_SCHEMA = \'' . $database . '\'');
		$tables = [];
		while ($row = $result->fetch_assoc())
		{
			$tables[] = $row;
		}
		return $tables;
	}
}
class Database extends discover
{
	use database_discoverability{
		database_discoverability::define_locations insteadof resource_discoverability;
	}
}
