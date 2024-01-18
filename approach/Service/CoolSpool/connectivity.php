<?php

namespace Approach\Service\CoolSpool;

use \Approach\Render\Node;
use \Approach\Service\Service;
use \Approach\Resource\CoolSpool\Spooler;
use \Approach\nullstate;
use \Stringable;

trait connectivity
{
	public $active_task = 'fetchFromCatalog';
	public $active_mode = 'Component v1.0';
	public bool $connected = false;
	public mixed $connection;
	public static $TokenMapperEndpoint = 'https://service.suiteux.com/TokenMapper.php';
	
	protected $alias=null;
	protected $current_statement;
	protected static $connection_limit=null;
	protected static $num_connected = 0;

	// Temporary idea holder
	public static $mapped_bindings = [
		'Component'		=> [],
		'Dataset'		=> [],
		'Resource'		=> [],
		'Composition'	=> [],
		'Collection'	=> [],
		'Catalog'		=> [],
		'Datamap'		=> [],
		'TokenMap'		=> [],
		'Connection'	=> [],
		'Query'			=> [],
		'Binding'		=> [],
		'Service'		=> [],
		'Imprint'		=> [],
		'Flow'			=> [],
		'Render'		=> [],
		'Aspect'		=> [],
		'Node'			=> [],
		'Data'			=> [],
		'Input'			=> [],
		'Output'		=> [],
		'Config'		=> [],
		'SSL'			=> [],
		'OIDC'			=> [],
		'SAML'			=> [],
	];

	/**
	 * Standard Service::Process function, takes in a list of payloads, and processes them
	 * In the common case, $payload[0] = json_decode( some api call JSON, true );
	 * There could be any number of payloads, from a message queue, or a list of files, etc.
	 * so all Services loop each payload, and Process them individually.
	 * 
	 * The Service::dispatch(){ connect, Request, Recieve, Process, Respond } cycle is mostly
	 * handled by the Service\Service class, but we need to override Process() to handle
	 * CoolSpool payloads.
	 */
	public function Process($payload = []) : void
	{
		// $this->payload was probably already set by json_decode( $x, true )
		// $this->payload = $payload ?? $this->payload;
		// $payload_type = $this->getPayloadType();

		if($this->active_mode == 'Component v1.0'){
			$this->Process_Component_v1_0($payload);
		}
	}

	/**
	 * Works exclusively with Approach v1 Components in v1 Compositor's API
	 */
	public function Process_Component_v1_0($payload = []) : void
	{
		$payload = $payload ?? $this->payload;
		$this->payload = [];

		foreach($payload as $response_or_request)
		{
			if($this->active_task === 'fetchFromCatalog'){	// Should really probably be done in Request() or connect()
				$this->payload[] = $this->fetchTypeCatalog_v1($response_or_request);
			}
		}
	}

	/**
	 * @method fetchFromCatalog 
	 * Uses Approach v1's Compositor API as a Catalog source
	 * 
	 * {
	 * 		"[ComponentType]": {
	 * 			"[resource_name]": [ 
	 * 				{}, {}, ... 
	 * 			],
	 * 			...
	 * 		},
	 * 		...
	 * }
	 * 
	 */

	public function fetchTypeCatalog_v1($response){
		$data = [];
		foreach($response as $ComponentType => $items){
			$binding = self::$mapped_bindings['Component'][$ComponentType] ?? self::getComponentBinding_v1($ComponentType);
			foreach($binding as $token) foreach($items as $item){
				$data[ $item['_self_id' ] ][ $token['name'] ] = $item[ $token['name'] ];
			}
		}
		return $data;
	}

	/**
	 * @method getComponentBinding_v1
	 * Fetches a Component's binding from the CoolSpool TokenMapper
	 * Relies on a properly set TokenMapper endpoint
	 * 
	 * {
	 * 		"payload" : [ 
	 * 			{
	 * 				"name"			:	"token_name",
	 * 				"type"			:	"text",
	 * 				"compose"		:	[
	 * 					{
	 * 						"source": 	"dataset::MyTable",
	 * 						"field"	:	"my_field",
	 * 						"meta"	:	{...}
	 * 					},
	 * 					...
	 * 				],
	 * 				"transformer"	:	"somePHPFunction"
	 * 			},
	 * 			...
	 * 		]
	 * }
	 * 
	 * @param string|\Stringable $which
	 * @return array $binding
	 */
	public static function getComponentBinding_v1(string|\Stringable $which='') : array {
		$binding=[];

		// Match the expected request format for  the TokenMapper endpoint
		// TODO: Make this a type of Node, such as Render/CoolSpool/BindingHeaderRequest
		// TODO: Make Encoder/Decoder for that Node type
		// TODO: Have TokenMapper use that Node type, such that $request->command, $request->support->dataset, etc. are available
		// WHY: Uses type constraints to enforce the structure of the request, and makes it easier to read in the end

		$json = [
			'command' => 'FetchDatasetHeader',
			'support' => [
				'dataset' => 'component::'.$which.'',
				]
			];
			
		// https://service.suiteux.com/TokenMapper.php?
		// 		json={"command":"FetchDatasetHeader","support":{"dataset":"component::MyTypeHere"}}
		$url =	self::$TokenMapperEndpoint . 
				'?json=' . urlencode( 
					json_encode( $json )
				);

		$response = file_get_contents($url);
		$binding = json_decode($response, true);

		self::$mapped_bindings['Component'][$which] = $binding['payload'];
		return self::$mapped_bindings['Component'][$which];
	}

	/**
	 * @method getPayloadType
	 * CoolSpool Service processes a few types of payloads:
	 * * * *
	 * Type ObjectList
	 * 	{
	 * 		"payload":[ {},	{}, ... ]
	 *  }
	 * 
	 * * * *
	 * Type CommandPair
	 *  {
	 * 		"command":"..."
	 * 		"support": {
	 * 			... 
	 * 		}
	 * 	}
	 * * * *
	 * Type MultiObjectList
	 * 	[ ObjectList, ObjectList, ... ]
	 * * * *
	 * Type MultiCommandPair
	 * 	[ CommandPair, CommandPair, ... ]
	 * * * *
	 * Type KeyedObjectList
	 * {
	 * 		"[key]" : ObjectList,
	 * }
	 * * * *
	 * Type KeyedCommandPair
	 * {
	 * 		"[key]" : CommandPair,
	 * 		"[key]" : CommandPair
	 * }
	 * 
	 * @return string $payload_type
	 */
	public function getPayloadType($payload = []) : string
	{
		$payload_type = 'unknown';
		$payload = $payload ?? $this->payload;
		
		$is_keyed = self::is_assoc($payload);
		$is_list = !$is_keyed;
		$has_payload_L0 = $is_keyed ? array_key_exists('payload', $payload) : false;
		$has_support_L0 = $is_keyed ? array_key_exists('support', $payload) : false;

		if($is_keyed && !$has_payload_L0 && !$has_support_L0){
			foreach($payload as $key => $p){
				$sub_type = $this->getPayloadType( $p );
				if($sub_type == 'ObjectList'){
					$payload_type = 'KeyedObjectList';
				}
				if($sub_type == 'CommandPair'){
					$payload_type = 'KeyedCommandPair';
				}
				// Only one type allowed per payload, so if we find one, and match it to keyed/list variety,
				// we have our answer
				break;
			}
		}
		elseif($is_list && !$has_payload_L0 && !$has_support_L0){
			foreach ($payload as $p) {
				$sub_type = $this->getPayloadType($p);
				if ($sub_type == 'ObjectList') {
					$payload_type = 'MultiObjectList';
				}
				if ($sub_type == 'CommandPair') {
					$payload_type = 'MultiCommandPair';
				}
				break;	// See above
			}
		}
		elseif($is_keyed && $has_payload_L0 && !$has_support_L0){
			$payload_type = 'ObjectList';
		}
		elseif($is_keyed && !$has_payload_L0 && $has_support_L0){
			$payload_type = 'CommandPair';			
		}
		else {
			$payload_type = is_array($payload) ? 'JSON' : 'bad payload';
		}
		if($payload_type === 'bad payload'){
			throw new \Exception('CoolSpool received a payload that it does not understand.');
		}
		return $payload_type;
	}

	// Check if an array is associative or sequential
	public static function is_assoc($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}

}
