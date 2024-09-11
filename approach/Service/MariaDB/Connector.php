<?php

namespace Approach\Service\MariaDB;

use \Approach\Resource\Resource;
use \Approach\Resource\Aspect\aspects;
use \Approach\nullstate;
use \Approach\path;
use \Approach\Scope;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;

use \Approach\Render\PHP\Concepts;
use \Approach\Resource\Aspect;
use \Approach\Resource\MariaDB\Database;
use \Approach\Resource\MariaDB\Server;
use \Approach\Resource\MariaDB\Aspect\Server as discoverable; // no sever.php exists yet in maraidb/aspect

use \MySQLi;
use \Stringable;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


class Connector extends Service
{
    use connectivity;

    public function __construct(
        public flow $flow = flow::in,
        public bool $auto_dispatch = false,
        public format $format_in = format::json,
        public format $format_out = format::json,
        public target $target_in = target::file,
        public target $target_out = target::file,
        public $input = null,
        public $output = null,
        public mixed $metadata = [],
        public ?bool $register_connection = true
    )
    {
        // parent::__construct($flow, $auto_dispatch, $format_in, $format_out, $target_in, $target_out, $input, $output, $metadata);
    }

    /**
     * Create or update a Resource definition from a MariaDB database source
     * 
     * @param Resource $which	Updates all tables in the database if null
     * 
     */
    // public function discover(): nullstate
    // {
	// 	foreach( $this->nodes as $server){

    //         $safe = substr($server->label, 0, 2) == 'p:' ?
    //             substr($server->label, 2) :
    //             $server->label;

    //         // Remove characters that are invalid for class names for this->label
    //         $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $safe);


    //         $resource_root = $resource_root ?? path::resource->get();
    //         $resource_ns = '\\' . Scope::$Active->project . '\\Resource';
    //         $name = $resource_ns . '\\MariaDB\\' . $safe;
            
    //         $server->__update_composer_autoloader(
    //             resource_root: NULL,
    //             resource_class: 'MariaDB\\' . $safe
    //         );

    //         echo PHP_EOL.PHP_EOL;
    //         exit( 'MariaDB\\' . $safe );

    //         // TO DO: figure out better symbol forwarding / looping mechanism
    //         $tmp = new $name(
    //             host:       $server->host,
    //             user:       $server->user,
    //             port:       $server->port,
    //             pass:       $server->pass,
    //             database:   $server->database,
    //             label:      $server->label,
    //         );

    //         discoverable::define(caller: $tmp);

    //         $server->__update_composer_autoloader(
    //             resource_root: NULL,
    //             resource_class: 'MariaDB\\' . $safe . '\\' . 'user_trait',
    //         );

    //         $server->__update_composer_autoloader(
    //             resource_root: NULL,
    //             resource_class: 'MariaDB\\' . $safe . '\\' . 'profile',
    //         );


    //         $server->discover();

    //         // $tmp = new $name(...)
    //         // $name::define(caller $tmp);


    //         // $tmp = Resource::get_package_name($server);
    //         // Server::define(caller: $tmp);
	// 	}
    //     //$schemas = $this->inventory();
    //     //$this->manifest_fields($schemas);
    //     return nullstate::defined;
    // }

    private function fetchSchemaNames()
    {
        $conn = false;
    }
    public function override($sql): ?array
    {
        $result = [];
        try
        {
            $result = $this->connection->query($sql);
        }
        catch (\Exception $e)
        {
            return null;
        }
        return $result;
    }

	/**
	 * Fetches a list of all available database names on the server
	 */
    private function inventory(): array
    {
		$sql = 'SHOW DATABASES;';
		$result = [];
		try
		{
			$q = $this->connection->query($sql);

			if( $q instanceof \mysqli_result ){
				while( $row = $q->fetch_assoc() ){
					$result[] = $row['Database'];
				}
			}
		}
		catch (\Exception $e)
		{
			return [];
		}        
        return $result;
    }

    /*	public function find(
		string $where 		= '/',
		?Aspect $sort		= null,
		?Aspect $weigh		= null,
		?Aspect $pick		= null,
		?Aspect $sift		= null,
		?Aspect $divide		= null,
		?callable $filter	= null,
		?string $as			= null
	): Resource|accessible|nullstate {

		return nullstate::ambiguous;
	}
*/
}
