<?php

namespace Approach\Service\CoolSpool;

use \Approach\Resource\Resource;
use \Approach\Resource\Aspect\aspects;
use \Approach\nullstate;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;

use \Approach\Render\PHP\Concepts;
use \Approach\Resource\Aspect;
use \Approach\Resource\CoolSpool\Spooler;
use \MySQLi;
use \Stringable;

class Connector extends Service
{
    use connectivity;

	/**
	 * Creates a new CoolSpool Connector to consume the Approach Composition API
	 * 
	 * Generally, you will only need to set $input to an array of URL endpoints.
	 * All other parameters parameters have useful defaults. You may wish to
	 * change target_out to target::file for preseeding a local cache for example.
	 * 
	 * Perhaps use target::database, with an appropiate Resource class as format_out, 
	 * for persisting results to a database, etc.
	 *
	 * @method __construct
	 * @param  flow $flow						// Data originates externally (flow::in) or from this system (flow::out)
	 * @param  bool $auto_dispatch				// Automatically dispatches the connector on instantiation
	 * @param  format $format_in				// format::json, format::xml, format::csv, ...
	 * @param  format $format_out				// The last format used as the Service's payload
	 */
    public function __construct(
        public flow $flow = flow::in,
        public bool $auto_dispatch = false,
        public format $format_in = format::json,
        public format $format_out = format::json,
        public target $target_in = target::transfer,
        public target $target_out = target::transfer,
        public $input = null,
        public $output = null,
        public mixed $metadata = [],
        public ?bool $register_connection = true
    )
    {
        // parent::__construct($flow, $auto_dispatch, $format_in, $format_out, $target_in, $target_out, $input, $output, $metadata);
    }

    /**
     * Create or update a Resource definition from a CoolSpool catalog source
     * 
     * @param Resource $which	Updates all collections in the catalog if null
     * 
     */
    public function discover(): nullstate
    {
		foreach( $this->nodes as $spooler){
			$spooler->discover();
		}
        return nullstate::defined;
    }

	public function Process($payload = []) : void
	{
		$this->discover();
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
	 * Fetches a list of all available catalog names on the spooler
	 */
    private function inventory(): array
    {
		$sql = 'SHOW CATALOGS;';
		$result = [];
		try
		{
			$q = $this->connection->query($sql);

			if( $q instanceof \mysqli_result ){
				while( $row = $q->fetch_assoc() ){
					$result[] = $row['Catalog'];
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
