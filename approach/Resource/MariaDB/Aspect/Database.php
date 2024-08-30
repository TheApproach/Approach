<?php

namespace Approach\Resource\MariaDB\Aspect;

// use \Approach\Resource\discoverability as resource_discoverability;
use \Approach\Resource\Aspect\discover;
use Approach\Scope;

// use Approach\Resource\Resource;

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

// trait database_discoverability
// {
// 	public static function define_locations($caller)
// 	{
// 		if ($caller->server->connection instanceof \Approach\nullstate)
// 		{
// 			throw new \Exception('Server is not connected, nullstate::' . $caller->server->connection->name);
// 		}
// 		//escape input
// 		$database = $caller->server->connection->real_escape_string($caller->database);

// 		//query for table names
// 		$result = $caller->server->connection->query('SELECT * FROM information_schema.TABLES WHERE TABLE_TYPE = \'BASE TABLE\' AND TABLE_SCHEMA = \'' . $database . '\'');
// 		$tables = [];
// 		while ($row = $result->fetch_assoc())
// 		{
// 			$tables[] = $row;
// 		}
// 		return $tables;
// 	}
// }
class Database extends discover
{
	// use database_discoverability, resource_discoverability {
	// 	database_discoverability::define_locations insteadof resource_discoverability;
	// }

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

    public static function define_qualities($caller)
    {
        // okay but still when I print out get_class_vars($caller::class) it doesn't show the constants
        // It shows a bunch of other stuff, but not the constants
        $symbols = [
            'NAME',
            'DATABASE',
            'SERVER_NAME',
            'RESOURCE_PROTO',
            'SERVER_CLASS',
            'RESOURCE_CLASS',
            'CONNECTOR_CLASS',
        ];

        $f = fopen('some.json', 'w');
        fwrite($f, json_encode(get_class_vars($caller::class), JSON_PRETTY_PRINT));
        fclose($f); // this was left open maybe related
        // let me try again

        $data = [];

        foreach (get_class_vars($caller::class) as $key => $value) {
            if (!isset($caller->{$key}) && defined($caller::class . '::' . $key)) {
                $data[$key] = constant($caller::class . '::' . $key);
            }
        }

        return ['symbols' => $symbols, 'data' => $data, 'path' => $caller::get_aspect_directory()];
    }
}
