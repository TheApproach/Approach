<?php
namespace Approach\Resource\MariaDB\Table;

use Approach\nullstate;
use Approach\Render\MariaDB\find;
use Approach\Render\Node;
use Approach\Resource\Resource;
use Approach\Resource\sourceability as root;
use Approach\Resource\MariaDB\Aspect\Table as discovery;
use \Approach\Resource\Aspect\Aspect;
use Stringable;

const locate = 0;
const pick = 1;
const sort = 2;
const weigh = 3;
const sift = 4;
const divide = 5;
const filter = 6;

const field = 0;
const feature = 1;
const operate = 2;
const quality = 3;
const quantity = 4;
const mode = 5;

trait sourceability
{
	// use the root trait
	use root;
	public static $numeric_types = [
		'tinyint',		'smallint',		'mediumint',	'integer',		'bigint',
		'float',		'double',		'decimal',		'int',			'bit'
	];

	private static function escape_string($data)
	{
		if ( !isset($data) or empty($data) ) return '';
		if ( is_numeric($data) ) return $data;

		$non_displayables = array(
			'/%0[0-8bcef]/',		// url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/',			// url encoded 16-31
			'/[\x00-\x08]/',		// 00-08
			'/\x0b/',				// 11
			'/\x0c/',				// 12
			'/[\x0e-\x1f]/'			// 14-31
		);
		foreach ( $non_displayables as $regex ){
			$data = preg_replace( $regex, '', $data );
		}
		return str_replace("'", "''", $data );
	}

	private static function isFunctionString($str)
	{

		$hasArgumentList = false;
		$cursor = 0;

		if ($cursor = strpos($str, '(', $cursor) !== false) {					// IF an opening ( is found
			if (strpos($str, '(', $cursor) === false) {							// IF there are no ( after that
				if ($cursor = strpos($str, ')', $cursor) !== false) {			// IF there IS a ) after that
					if ($cursor == strlen($str)) {								// IF there are no characters after )
						$hasArgumentList = true;
					}
				}
			}
		}
		return $hasArgumentList;
	}

	public static function sanitize_picks($picks, $profile){
        $result = [];
        foreach($picks as $index => $pick){
            // verify that pick is found in $fields. if so add backticks around it, else set to blank string
            if($profile::field_match($pick) !== null && is_string($pick)){
                $result []= '`'.$pick.'`';
            }
        }
        return $result;
    }
	public static function sanitize_weights($weights, $profile){
		// format of a weight is... [ 0 => $field, 1 => $op, 2 => $value, 'weights' => [ 'value' => $weight ]] 
		// check if the first is a weight

		$result = [];
		foreach($weights as &$w){
			$field 	= $w[0]; 
			$op 	= $w[1];
			$value 	= $w[2];
			$weight = $w['weights']['value'];
			$aspect = [];

			if($profile::field_match($field) && is_string($field) ){
				$aspect['field'] = '`'.$field.'`';
				$aspect['operator'] = $op;
				$aspect['weight'] = is_numeric(($weight + 0)) ? ($weight + 0) : 0;
				$type = $profile::field_getType($field);
				if( in_array($type, static::$numeric_types)){
					$aspect['value'] = $value + 0;
					if( !is_numeric($aspect['value'])){
						continue;	//failed to convert to number? skip
					}
					else{
						$aspect['value'] = '"'.static::escape_string($value).'"';
					}
					$result []= $aspect;
				}
			}
		}
		return $result;
	}
	public static function sanitize_sifts($sifts, $profile)
	{
		//TODO
		$result = [];
		foreach($sifts as &$sift){
			// format of a sift is: [$field, $op, $value]
			// this is correct 
			$field 	= $sift[0];
			$op 	= $sift[1];
			$value 	= $sift[2];

			//check if field's source_type is one of the static::$numeric_types
			// if yes: $value = $value + 0;
			// if no: $value = '\''.static::escape_string($value) .'\'';
			$aspect = [];

			if($profile::field_match($field) && is_string($field) ){
				$aspect['field'] = '`'.$field.'`';
				$aspect['operator'] = $op;
				$type = $profile::field_getType($field);
				if( in_array($type, static::$numeric_types)){
					$aspect['value'] = $value + 0;
					if( !is_numeric($aspect['value'])){
						continue;	//failed to convert to number? skip
					} 
					else {
						$aspect['value'] = '"' . static::escape_string($value) . '"';
					}
					$result[] = $aspect;
				}
			}
		}
		return $result;
	}

	public static function pull(Stringable|string|Node|Resource $where): ?Node{
		$profile = $where::GetProfile();
		$source = $where::GetSource();

		$picks 		= static::sanitize_picks($where->_approach_resource_context[ pick ], 	$profile);
		$weights 	= static::sanitize_weights($where->_approach_resource_context[ weigh ], $profile);
		$sifts 		= static::sanitize_sifts($where->_approach_resource_context[ sift ], 	$profile);

		$query = new find(
			picks: 		$picks,
			weights: 	$weights,
			needs:   	$sifts,
			source: 	$source
		);

        print_r($query);

		$state = $where->server->connection->query($query->render());
		if($state == nullstate::ambiguous)
			return null;
		else
			return $where;
	}


	public static function aquire(Stringable|string|Node $where): ?Node{ return new Node; }
	 // public static function pull(Stringable|string|Node $where): ?Node{ return new Node; }
    public function load(): ?Node{ 
        self::aquire($this);
        self::pull($this);
        return $this;
    }

	public static function save(Resource $where): ?bool{ return true; }
	public static function push(Resource $where): ?bool{ return true; }
	public static function release(Resource $where): ?bool{ return true; }


	// Create a Service child class with MyConnector::connect() and MyConnector::disconnect()
	// Flow::IN - The service starts by consuming external sources
	// promise
	// if !connected, connect
	// if !acquired source, acquire source
	// if !loaded source resource(s), load source resource(s)
	// or
	// Flow::OUT - The service is creating output for external consumers
	// promise
	// if !connected, connect
	// if !acquired target, acquire target
	// push pre-acquired resource to target
	public static function transport(){}

	// Derrive branchable->promise() from Service\Branch to define promises
	public static function promise(){}

	/**
	 *  promise 
	 * 	- if !connected, connect
	 * 	- if !acquired $where, acquire $where
	 *	- send $intent & $support to acquired $where
	 * 	- recieve response resource 
	 */
	public static function act($where, $intent, ...$support){}

	// two-way transport 
	// from Service|Resource pair to Service|Resource pair
	public static function exchange(){}
	// {
	// if !connected, connect
	// if !acquired target, acquire target

	// acquire 

	// transport() source resource with Flow::IN
	// transport() to target resource with Flow::OUT

	// promise
	// push source resources to target location & release if possible
	// recieve response resource
	// if !released, release target
	// return response resource

	// see approach/Dataset/exchangeTransport->prep_exchange()
	// }
	 
	// {
	/**
	 * $from and $to both describe location and entity (who have associated RBAC permissions and security realm) to act on
	 * every endpoint inherits from the endpoint above it, so 
	 *  - mariadb://database/table/column inherits from 
	 *  - mariadb://database/table which inherits from 
	 *  - mariadb://database which inherits from mariadb:// which inherits from
	 *  - // which is controlled by the conductor.orchestra security realm in a separate project
	 * 
	 * Aside from the conductor.orchestra security realms, each endpoint in the system matches one or more security realms, 
	 * which are the Approach Layers:
	 * 
	 * 	- Work			- Literally the work being done. The CPU processes moving bits around. Usually no need to operate here.
	 * 	- Render		- The CPU processes creating output. Controlled by the Approach\Render\Node|Stream family of classes.
	 * 	- Imprint		- Renderable trees generated according to Pattern files. Controlled by the Approach\Imprint family of classes.
	 * 	- Resource		- Existing patterns (Approach or 3rd party such as API/DBs/files..) whose source must be repeated into the system and parsed into a structure. Controlled by the Approach\Resource family of classes.
	 * 	- Component		- Aggregations of Resources into a singular unit over an Imprinted Pattern. Controlled by the Approach\Component family of classes.
	 *  - Composition	- Output structuring with dynamic calling of Component streams. Controlled by the Approach\Composition family of classes.
	 * 	- Service		- System-wide typecasting, format conversion, transport and security. Access Compositions and Component APIs. Controlled by the Approach\Service family of classes.
	 *  - Instrument	- Representation of servers, virtual machines, containers - one IP or equivalent network identifier. Controlled by devops tooling. Classes pending.
	 *  - Ensemble		- Representation of a cluster of Instruments. Controlled by devops tooling. Classes pending.
	 *  - Orchestra		- Representation of a service mesh of Ensembles. Controlled by devops tooling. Classes pending.
	 * 
	 * Roles in the system are defined by the security realms they match, and the security realms they inherit from.
	 * Roles may have the following permissions:
	 * 
	 * Entity Ability Grants:
	 *	- Grant			- Grant access to a federated typepath
	 *  				  A role with Grant permission may give any permission they hold to any other role
	 * 					  Exception: A role may be altered by a whitelist/blacklist specific typepaths
	 * 	- Create		- Create a federated entity (org, group, project, instrument, role, etc)
	 * 	- Read			- Read access to a federated entity
	 * 	- Update		- Update a federated entity (write on update)
	 * 	- Replace		- Replace a federated entity (overwrite, updating references and identifiers)
	 * 	- Tennent		- This entity may have tennents (sub-entities)
	 *  - Service		- This entity may have an API key to access a federated entity
	 *  - Masquerade	- This entity may impersonate another entity
	 * 
	 * 
	 * Resource Ability Grants:
	 *	- Revoke		- Revoke access to a federated typepath
	 *	- Create		- Create a federated typepath (write on create)
	 *	- Read			- Read access to a federated typepath
	 *	- Update		- Update a federated typepath (write on update)
	 *	- Replace		- Replace a federated typepath (overwrite, updating references and identifiers)
	 *	- Run			- Execute access to a federated typepath
	 *	- Remove		- Delete a federated typepath (remove from context) 
	 *	- Destroy		- Destroy a federated typepath (cascade delete until source or fail)
	 *	- Lock			- Lock/Unlock a federated typepath
	 *	- Connect		- Connect to a federated typepath
	 *	- Secure		- OpenID & X509 authentication, authorization and encryption/decryption
	 */
	// }
	public static function bestow($from, $to, $who) {
	}

	public static 	function locate($where){
		return self::find($where);
	}

	public static function interact(){}
	// robust fetch
}
