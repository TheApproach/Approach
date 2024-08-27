<?php

namespace Approach\Service\MariaDB;

use \Approach\Resource\Resource;
use \Approach\Resource\Aspect\aspects;
use \Approach\nullstate;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;

use \Approach\Render\PHP\Concepts;
use \Approach\Resource\Aspect;
use \Approach\Resource\MariaDB\Server;
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
    public function discover(): nullstate
    {
		foreach( $this->nodes as $server){
			$server->discover();
            Server::define(caller: $server);
		}
        //$schemas = $this->inventory();
        //$this->manifest_fields($schemas);
        return nullstate::defined;
    }

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
