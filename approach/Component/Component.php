<?php

/*************************************************************************

 APPROACH

*************************************************************************/


namespace Approach\Component;

use \Approach\path;
use \Approach\nullstate;
use \Approach\Render;
use \Approach\Resource\Resource;


/* Delete this * to see dependency bugs --> */
class Dataset{
	public $data=[];
	function save($a=null,$b=null){return true;}	
}
class Smart extends Render\HTML{
	public $data = [];
	public $TemplateBinding=[];
	public $Context=[];
	public function BindContext(){}
	public function Tokenize(){}
}

function LoadObject($target, $options){return new Dataset;}
function LoadObjects($target, $options){return [];}
function RegisterScript($script, $is, $name){}
function getPlural($c){return '';}
/***/
class Component
{
	public static $SubcomponentName = 'Component';
	public static $shared = [];

	protected static $SaveFlag = [];
	protected $UpdateDataclass = [];

	public $ComponentData;
	public $context = [];
	public $sources = [];

	/***************************************************************************//*
     * @type string|enum|null $ImprintPattern Defines the class of @see renderable used to contain template results.
     * @type string|array|null $ChildTag Defines the inner element @see renderable::$tag of the templates of  used.
     * @type string|array|null $ChildClasses Defines the CSS $see renderable::classes applied to the outermost element(s) the template(s) used.
     * @type string|array|null $ChildClasses Defines the XML $see renderable::attributes applied to the outermost element(s) the template(s) used.
     * @type string|array|null $ParentContainer Defines a reference to the @see renderable used to contain template results.
     * @type string|array|null $this->ContainerClasses Defines the CSS @see renderable::classes applied to the @see renderable used to contain template results.
     *****************************************************************************/

	public $TemplateCount = 0;
	
	public function __construct(
		// TODO: Consolidate Child variables into $DefaultChild = new Render\Type
		// TODO: Consolidate Container variables into $DefaultContainer = new Render\Type

		public ?Resource $resource				= null,								/**		reference to a Resource for token values	*/
		public string|Render\Node $pattern		= new Render\Node,					/**		reference to a pattern node or path to use	*/
		public array $binding					= new Render\Node,					/**		map of Resource fields to Imprint tokens	*/

		public $ImprintPattern						= '\\'.Render\Node::class,			/**		type of Render noed to use as container		*/
		public $ChildTag						= [],								/**		tags to apply to the selected imprint 		*/	
		public $ChildClasses					= [],								/**		classes to apply to the selected imprint 	*/
		public $ChildAttributes					= [],								/**		attributes to apply to the selected imprint */
		public $Container						= null,								/**		reference to actual parent container		*/
		public $ContainerTag					= 'div',							/**		tag to apply to the group container			*/
		public $ContainerClasses				= [],								/**		classes to apply to the group container	 	*/
		public $ContainerAttributes				= [],								/**		attributes to apply to the group container	*/
		public $Scripts							= '',								/**		list of javascript	 						*/
		public $ScriptPlacement					= true,								/** 	true: head, false: tail of body				*/
		public $prefetch						= []								/**		vaules obtained externally					*/
	)
	{
		//Default Values
		if (!isset($this->ContainerClasses))	$this->ContainerClasses	= array(static::class . 'Container');
		if (!isset($this->ChildClasses))		$this->ChildClasses		= array(static::class);
		$this->Container->classes[] = new Render\Attribute(NULL, static::class . 'Container');

		$this->Container = &$this->Container ?? new $this->ImprintPattern(
			...
			[
				'tag'		=> $this->ContainerTag,
				'classes'	=> $this->ContainerClasses,
				'attributes'=> $this->ContainerAttributes
			]
		);
		$this->context['root']		= &$this->Container;
		$this->context['render']	= &$this->ImprintPattern;
		$this->context['data']		= &$resource;
		$this->context['pattern']	= &$pattern;
		
		$this->sources['data']		= &$resource;
		$this->sources['pattern']	= &$pattern;
		$this->sources['binding']	= &$binding;
		$this->sources['prefetch']	= &$prefetch;

		$this->ComponentData = &$this->sources['data'];
		
		return $this->Container;
	}

	function HandleChildScripts($child)
	{
		if (isset($child->Scripts['head'])) {
			foreach ($child->Scripts['head'] as $Name => $Script) {
				RegisterScript($Script, true, $Name);
			}
		}
		if (isset($child->Scripts['tail'])) {
			foreach ($child->Scripts['tail'] as $Name => $Script) {
				RegisterScript($Script, false, $Name);
			}
		}
	}

	function HandleScripts($wrapper)
	{
	}

	function Create($support)
	{
		global $SupportPath;
		global $ApproachConfig;
		$ApproachConfig['EditMode']['active'] = true;

		$data_defaults = [];
		$token_defaults = [];

		$selector = 'selectedComponent()';
		$opts = [
			'method'    =>  'WHERE `dataset` = \'component::' . static::class . '\'  AND `tag` = \'tokens\' ',
			'condition' =>  'ORDER BY `when` DESC LIMIT 1',
			// 'debug' => true
		];

		$token_map = json_decode(
			(LoadObject('datamaps', $opts))->data['binding'],
			true
		);
		if (empty($token_map)) {
			exit('{"error":"No token map found for default values."}');
		}

		foreach ($token_map as $token) {
			$name = $token['name'];
			$default = $token['default'];
			$source = explode('::', $token['composed'][0]['source'])[1];
			$source_field = $token['composed'][0]['field'];

			$data_defaults[$source][$source_field] = $default;
			$token_defaults[static::class][$name] = $default;
		}


		$pattern = $SupportPath . '/templates/' . static::class . '.xml';
		if (!empty($support['pattern'])) {
			if (ctype_alnum($support['pattern']) && preg_match('/\s/', $support['pattern']))
				$pattern = $SupportPath . '/templates/' . $support['pattern'];
			else exit('{"error": C08, "msg":"Provided template had illegal characters."}');
		}


		$null_root = new Smart(
			tag: NULL
		);
		$null_root->tag = !empty($this->ContainerTag) ? $this->ContainerTag : 'div';
		if (!empty($this->ContainerClasses))
			$null_root->classes = $this->ContainerClasses;
		if (!empty($this->ContainerAttributes))
			$null_root->attributes = $this->ContainerAttributes;

		$null_root->classes[] = 'Component';
		$null_root->classes[] = static::class;
		$null_root->attributes['data-component'] = static::class;

		$binding = $null_root->TemplateBinding[static::class];

		$source = array_keys($binding)[0];		// Get first table from template binding

		$accessor = $source::$profile['Accessor']['Primary'];

		$spawned = new $source($source);
		// $spawned->options['debug']=true;
		$spawned->create($data_defaults[$source]);

		$cid = $spawned->data[$accessor];
		$cid_formatted = (is_string($cid) && !is_numeric($cid)) ? '"' . $cid . '"' : (int)$cid;
		$spawned = LoadObject($source, [
			'condition'	=>	'WHERE ' . $accessor . ' = ' . $cid_formatted
		]);

		// make sure CID is assigned component -sel fo someting

		// TODO: instead of loading, use the spawned. Tell options that it's prefetched
		// $options['prefetch'] = $spawned->data;     maybe how to do it
		$options[static::class][$source] = [
			'target' => $source,
			'new_query' => true,
			'method' => 'WHERE `' . $accessor . '` = ' . $cid_formatted . ' LIMIT 1'
		];
		$null_root->BindContext();

		$Context = $null_root->Context[static::class];
		$Component = new (static::class)();
		// $Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['pattern']);
		$Component->Load($options[static::class]);

		$null_root->attributes['data-component'] = static::class;
		$null_root->classes[] = 'ComponentGroup';

		if (!empty($null_root->nodes[0]) && empty($null_root->nodes[0]->attributes['data-self'])) {
			$null_root->nodes[0]->attributes['data-self'] = $cid;
		} else {
			$null_root->attributes['data-self'] = $cid;
		}

		if (!empty($support['_response_target']))
			$selector = $support['_response_target'];
		return ['render' => $null_root->render(), 'selector' => $selector, 'self' => $cid];
	}

	public function SetOwnership($item, $support = [])
	{
		return $item;
	}

	public function CreateSubcomponent($support)
	{
		global $SupportPath;
		global $ApproachConfig;
		$ApproachConfig['EditMode']['active'] = true;

		$which_component = $support['which_component'];
		$SubcomponentName = $which_component::$SubcomponentName;
		$temp = new $SubcomponentName();

		$Subcomponent = $temp->Create([]);

		$sub_source = reset($temp->sources);
		$sub_accessor = $sub_source::$profile['Accessor']['Primary'];
		$cid_formatted = (is_string($Subcomponent['self']) && !is_numeric($Subcomponent['self'])) ? '"' . $Subcomponent['self'] . '"' : (int)$Subcomponent['self'];
		$item = LoadObject($sub_source, [
			'method'	=>	'WHERE `' . $sub_accessor . '` = ' . $cid_formatted
		]);

		$accessor = $sub_source::$profile['Accessor']['Primary'];

		// $spawned = new $sub_source($sub_source);
		// // $spawned->options['debug']=true;
		// $spawned->data =
		$item;

		$temp->SetOwnership($item, $support);
		$item->save();

		$intent_support = [
			'_self_id'			=>	$Subcomponent['self'],
			"which_component"	=>	$SubcomponentName
		];

		$intent_support =
			htmlspecialchars(
				json_encode(
					$intent_support
				),
				ENT_QUOTES
			);

		$item = new Render\HTML(...[
			'tag'					=>	'li',
			'classes'				=>	['control'],
			'attributes'			=>	Render\Attribute::fromArray([
				'data-action'		=>	'Settings_Subcomponent_Open',
				'data-role'			=>	'trigger',
				'data-component'	=>	$SubcomponentName,
				'data-support'		=>	$intent_support
			]),
			'content'				=>	'New ' . $SubcomponentName
		]);

		return [
			'render' => $item->render(),
			'selector' => 'new_item'
		];
	}


	function getPlural($noun)
	{
		switch (substr($noun, -2)) {
			case 'ty':
				return substr($noun, 0, -2) . 'ties';
			case 'us':
				return substr($noun, 0, -2) . 'i';
			case (substr($noun, -1) == 'x'):
				return $noun . 'es';
			case (substr($noun, -1) == 's'):
				return $noun . 'es';
			default:
				return $noun . 's';
		}
	}


	function Data($support)
	{
		global $SupportPath;

		$options = [];
		$cid = $support['component_id'];

		$Component = new (static::class)();
		$source = isset($this->sources) ? reset($this->sources) : getPlural(static::class);
		$PrimaryKey = $source::$profile['Accessor']['Primary'];

		$options[static::class][$source] = ['target' => $source, 'new_query' => true, 'method' => 'WHERE `' . $PrimaryKey . '` = ' . $cid . ' LIMIT 1'];
		$options['pattern'] = $SupportPath . '/templates/' . static::class . '.xml';

		$t = $Component->ChildTag ?? 'div';
		$t = $Component->ContainerTag ?? $t;

		$ComponentContainer = new $this->ImprintPattern( ...[ 'tag' => $t, ...$options] );
		$ComponentContainer->BindContext();

		$Context = $ComponentContainer->Context[static::class];
		// $Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['pattern']);
		$Component->Load($options[static::class]);


		$ComponentContainer->render();
		$active_tokens = $ComponentContainer->nodes[0]->tokens;

		foreach ($active_tokens as $key => $value) {
			if (substr($key, 0, 2) == '__')
				unset($active_tokens[$key]);
		}
		return ['render' => $active_tokens, 'selector' => 'data'];
	}

	function Load($options = array()) //smart ImprintPattern
	{

		//Optional Overrides
		if (is_array($options)) {
			foreach ($options as $key => $value) {
				switch ($key) {
					case 'ImprintPattern':
						$this->ImprintPattern		= $value;
						break;
					case 'ChildTag':
						$this->ChildTag		= $value;
						break;
					case 'ContainerClasses':
						$this->ContainerClasses	= $value;
						break;
					case 'ContainerAttributes':
						$this->ContainerAttributes	= $value;
						break;
					case 'ChildClasses':
						$this->ChildClasses		= $value;
						break;
					case 'Scripts':
						$this->Scripts		= $value;
						break;
					case 'ScriptPlacement':
						$this->ScriptPlacement	= $value;
						break;
					case 'context':
						foreach ($value as $context => &$variable) {
							switch ($context) {
								case 'root':
									$this->context['root']		= &$variable;
									break;
								case 'render':
									$this->context['render']	= &$variable;
									break;
								case 'data':
									$this->context['data']		= &$variable;
									break;
								case 'pattern':
									$this->context['pattern']	= &$variable;
									break;
									//case 'prefetch': 	$this->prefetch	= $variable; break;
								default:
									break;
							}
						}
					default:
						break;
				}
			}
		}

		global $ApproachConfig;
		$EnvironmentVariables = [];
		foreach ($ApproachConfig as $key => $collection) foreach ($collection as $sub_key => $value) {
			$EnvironmentVariables[$key . '.' . $sub_key] = $value;
		}

		//var_exp($this->prefetch);

		/*	END FETCHING OPTIONAL VALUES	*/
		/* ---------------------------------*/
		/*	BEGIN ARRAY ALIGNMENT			 */

		//var_export($this->context['data']);
		foreach ($this->context['data'] as $_class) {
			if (empty($this->prefetch[$_class])) {
				if ($_class != '__approach') {
					$_instances = LoadObjects($_class, $options[$_class]);
				}
			} else {
				$_instances = $this->prefetch[$_class];
			}
			$i = 0;

			foreach ($_instances as $_instance) {
				foreach ($_instance->data as $key => $value) {
					$BuildData[$i][$_class][$key] = $value;   // This is the line that gets field values ready for tokenization
					//$BuildData[$i]['__approach'] = $EnvironmentVariables;
				}




				if (isset(static::$SaveFlag[$i]))
					if (static::$SaveFlag[$i] == true)
						$this->UpdateDataclass[$_class] = $_instance->data;
				++$i;
			}
		}


		/*	ARRAYS FROM DATA SOURCE ALIGNED TO $ConsolidatedRow	*/
		/* ---------------------------------------------------- */
		/*	CONTINUE, SEND NORMALIZED DATASOURCES TO PROCESSING	 */

		//  Note: This is an optimal place to generalize different data schemes
		//  For example, if you want to load a complex data type, flatten the
		//  keys or paths to
		//  $BuildData[$i][ 'path/based/29/nested/key'  ]    or
		//  $BuildData[$i][  'root.child[42].owner_id->'  ]

		@$this->ComponentData = $BuildData;
		$this->Process($BuildData);
	}


	function Process(&$BuildData)
	{
		global $RemoteBase;
		global $ApproachConfig;

		//var_export($BuildData);

		$this->PreProcess($BuildData);
		$TemplateCount = $this->AlignMarkup();
		$this->TemplateCount = $TemplateCount;
		$Children = [];
		if ($BuildData === NULL) $BuilData = array();

		if (!empty($BuildData))
			for ($b = 0, $BL = count($BuildData); $b < $BL; ++$b) // as $ConsolidatedRow)
			{
				$ConsolidatedRow = $BuildData[$b];
				if (isset($this->shared['data'])) array_merge($ConsolidatedRow, $this->shared['data']);
				$c = 0;
				for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
					//Send Data From Database To Rendering Engine
					$SmartObject = new $this->ImprintPattern(
						array(
							'tag' => $this->ChildTag[$i],
							'pattern' => $this->context['pattern'],
							'markup' => $i
						)
					);
					$SmartObject->tokens['__self_index'] = $c;
					$SmartObject->tokens['__remote_base'] = $RemoteBase;

					//var_dump($ParentContainer);

					// In a project's core.php
					// create $ApproachConfig['collection']['key'] = value;
					// to include __collection_key as a global template token
					foreach ($ApproachConfig as $key => $collection) foreach ($collection as $sub_key => $value)
						$SmartObject->tokens['__' . $key . '.' . $sub_key] = $value;


					$SmartObject->data[static::class] = (isset($SmartObject->data[static::class])) ?
						array_merge($SmartObject->data[static::class], $ConsolidatedRow) : $SmartObject->data[static::class] = $ConsolidatedRow;

					$SmartObject->classes = (is_array($SmartObject->classes)) ?
						array_merge($SmartObject->classes, $this->ChildClasses[$i]) : $this->ChildClasses[$i];

					$SmartObject->attributes = (is_array($SmartObject->attributes)) ?
						array_merge($SmartObject->attributes, $this->ChildAttributes[$i]) : $this->ChildAttributes[$i];

					if (empty($this->Container->options['direct']) || $this->Container->options['direct'] !== true)
						$SmartObject->Tokenize();


					if (!empty($SmartObject->tokens['_self_id'])) {
						$SmartObject->attributes['data-self'] = $SmartObject->tokens['_self_id'];
					}
					$SmartObject->classes[] = 'ComponentInstance';
					$SmartObject->classes[] = static::class . 'Layout';

					if ($this->InProcess($ConsolidatedRow, $SmartObject, $ParentContainer, $c, $i) === false) continue;
					$this->Container->nodes[] = $SmartObject;
					$this->HandleChildScripts($SmartObject);

					++$c;
				}
				//$SmartObject->Tokenize();
			}
		$this->HandleScripts($this->Container);

		//$this->Container->nodes=array_merge($this->Container->nodes, $Children);
		$this->Container->classes = array_merge($this->Container->classes, $this->ContainerClasses);
		$this->PostProcess($BuildData, $this->Container);
	}

	function InProcess(&$ConsolidatedRow, &$SmartObject, &$ParentContainer, $markupIndex, $itemIndex)
	{
	}
	function PreProcess(&$BuildData)
	{   /* $this->Container->nodes[]= $t=new renderable('div');    */
	}
	function PostProcess(&$BuildData, &$container)
	{
	}

	function Push($support)
	{
		global $SupportPath;

		$options = [];
		$full_mode = isset($support['component://' . static::class]);
		$action_support = !empty($support['action://Settings.Container.Save']) ? $support['action://Settings.Container.Save'] : [];
		$container_settings = [];
		if (isset($action_support['container_settings']) && $action_support['container_settings'] == 'component') {
			$container_settings = $action_support['container_settings'];
		} else foreach ($action_support as $container) {
			if (isset($action_support['container_settings']) && $action_support['container_settings'] == 'component') {
				$container_settings = $action_support['container_settings'];
			}
		}

		$cid = $support['component_id'];					// Original component instance in the slot

		$ComponentName = static::class;
		if (isset($support['component://' . $ComponentName])) {
			if (isset($support['component://' . $ComponentName]['__self_id'])) {
				// Potentially swapped id for new component instance in the slot
				$cid = $support['component://' . $ComponentName]['__self_id'];
				if (is_numeric($cid))
					$cid = (int) $cid;
			}
		}

		$Component = new $ComponentName();
		$tmpComponent = new $ComponentName();

		$source = reset($Component->sources);
		$PrimaryKey = $source::$profile['Accessor']['Primary'];

		$options[$ComponentName][$source] = ['target' => $source, 'new_query' => true, 'method' => 'WHERE `' . $PrimaryKey . '` = ' . $cid . ' LIMIT 1'];
		$options['pattern'] = $SupportPath . '/templates/' . $ComponentName . '.xml';

		$t = $Component->ChildTag ?? 'div';
		$t = $Component->ContainerTag ?? $t;

		$tmpComponentContainer = new $this->ImprintPattern( ...[ 'tag' => $t, ...$options] );
		$tmpComponentContainer->BindContext();

		$ComponentContainer = new $this->ImprintPattern( ...[ 'tag' => $t, ...$options] );
		$ComponentContainer->BindContext();

		// To DO: Authorize User again
		static::$SaveFlag[0] = true;


		$tmpContext = $tmpComponentContainer->Context[$ComponentName];

		$tmpComponent->createContext($tmpContext['self'], $tmpContext['render'], $tmpContext['data'], $tmpContext['pattern']);
		$tmpComponent->Load($options[$ComponentName]);

		// var_export($Component->UpdateDataclass);
		//
		// $valid_token_arrays = is_array($support['token']) && is_array($support['value']);
		// $valid_token_arrays = $valid_token_arrays && count($support['token']) == count($support['value']);
		$UpdateTokens = [];

		// support[component://ComponentName] is set, update multiple tokens
		if ($full_mode) {
			// $token_pack = $support['component://'.static::class];
			foreach ($support['component://' . static::class] as $token => $value) {
				if (!str_starts_with($token, '_')) continue;
				$UpdateTokens[$token] = $value;
			}
		}
		// support[token] and support[value] are set, only a single token is being updated
		else {
			$UpdateTokens = [
				$support['token'] => $support['value']
			];
		}

		$tmpComponent->Save($UpdateTokens, $ComponentContainer->TemplateBinding);


		$Context = $ComponentContainer->Context[$ComponentName];
		$Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['pattern']);

		$Component->Load($options[$ComponentName]);

		if ($full_mode) {
			// Push multiple tokens
			$ComponentContainer = $Component->context['root'];
			$ComponentContainer->attributes['data-component'] = static::class;
			$ComponentContainer->attributes['data-container-settings'] = htmlspecialchars(json_encode($container_settings), ENT_QUOTES);
			$ComponentContainer->classes[] = 'Component ' . static::class . ' Interface controls ComponentGroup';

			foreach ($Component->context['root']->nodes as $ComponentInstance) {
				$ComponentInstance->attributes['data-self'] = $cid;
				$ComponentInstance->classes[] = 'editable ComponentInstance';
			}
		}


		$json = [];
		foreach ($Component as $key => $value) {
			$json[$key] = NULL;
		}

		// TODO $Component->context['root']->applyContainerOptions( $container_settings );
		$Component->context['root']->pageID = null;
		$WorkData['render'] = $Component->context['root']->render();

		$WorkData['selector'] = empty($support['_response_target']) ? 'selectedComponent()' : $support['_response_target'];
		return $WorkData;
	}


	function Save($IncomingTokens, $TemplateBinding)
	{
		global $DataPath;
		$change = false;
		// echo 'a';
		foreach ($TemplateBinding as $Component => $_Dataclass) {
			// echo 'b';
			$ActiveComponent = $Component;
			foreach ($_Dataclass as $source_name => $field_list) {
				if (isset($this->UpdateDataclass[$source_name])) {
					// echo 'd';
					require_once($DataPath . '/' . $source_name . '.php');
					$dbo = new $source_name($source_name);
					$dbo->data = [];

					foreach ($this->UpdateDataclass[$source_name] as $k => $v)   $dbo->data[$k] = $v;

					// Loop through all property => token assignments from this Component's template JSON
					//
					foreach ($field_list as $field => $TokenName) {
						if (isset($source_name::$profile['header'][$field]) && isset($IncomingTokens['_' . $TokenName])) {
							if ($IncomingTokens['_' . $TokenName] != $dbo->data[$field]) {
								$change = true;
								$dbo->data[$field] = $IncomingTokens['_' . $TokenName];
							}
						}
					}
					if ($change) {
						// echo PHP_EOL.PHP_EOL;
						// $dbo->options['debug'] = 'debug';
						$dbo->save($dbo->data[$dbo::$profile['Accessor']['Primary']]);
						// echo ' save() ';
					}
				}
			}
		}
		return 'CLEAR';
	}

	function AlignMarkup()
	{
		$Children = array();

		$TemplateCount = count($this->Container->markup);
		if ($this->Scripts != '')	RegisterScript($this->Scripts, $this->ScriptPlacement, $this->ImprintPattern . ' ' . static::class);

		$TChildClasses = $this->ChildClasses;
		$TChildAttributes = $this->ChildAttributes;
		$TChildTag = $this->ChildTag;
		$this->ChildClasses = array();
		$this->ChildAttributes = array();
		$this->ChildTag = array();

		if (is_array($TChildClasses)) {
			if (is_array(reset($TChildClasses)))						$this->ChildClasses = array_values($TChildClasses);
			else {
				for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
					$this->ChildClasses[$i] = $TChildClasses;
				}
			}
		} else {
			for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
				$this->ChildClasses[$i] = $TChildClasses;
			}
		}

		if (is_array($TChildAttributes)) {
			if (is_array(reset($TChildAttributes))) {
				$this->ChildAttributes = array_values($TChildAttributes);
			} else {
				for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
					$this->ChildAttributes[$i] = $TChildAttributes;
				}
			}
		} else {
			for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
				$this->ChildAttributes[$i] = $TChildAttributes;
			}
		}

		if (is_array($TChildTag))										$this->ChildTag = $TChildTag;
		else {
			for ($i = 0, $L = $TemplateCount; $i < $L; ++$i) {
				$this->ChildTag[$i] = $TChildTag;
			}
		}

		/*	END ARRAY ALIGNMENT */
		return $TemplateCount;
	}


	/// DATA MAPPING FUNCTIONALITY


	public function FetchDatasetHeader($source)
	{
		global $LocalServicePath;
		$source_meta = [];

		$file = file_get_contents($LocalServicePath . '/component_bindings/' . static::class . '.json');
		$json = json_decode($file, true);

		foreach ($json as $fieldname => $sample_value) {
			$source_meta[] = [
				'name'      =>  $fieldname,
				'type'      =>  'text',
				'composed'  =>  empty($sample_value['composed']) ? [] : $sample_value['composed'],
				'transformer' =>  null
			];
		}
		return ['payload' => $source_meta];
	}


	public function Data2($support)
	{
		global $SupportPath;
		$cid = $support['component_id'];
		$options = [];

		$i = 0;
		foreach ($this->sources as $src) {
			$options = self::GenerateSelectQueryForRemoteInstance($src, $cid, $options, $i);
			$i++;
		}

		$options['pattern'] = $SupportPath . '/templates/' . static::class . '.xml';

		$t = $this->ChildTag ?? 'div';
		$t = $this->ContainerTag ?? $t;

		$ComponentContainer = new $this->ImprintPattern( ...[ 'tag' => $t, ...$options] );

		$ComponentContainer->BindContext();
		foreach ($options as $CName => $ops) {
			if ($CName !== 'pattern')
				foreach ($ops as $src => $optList) {
					$optList['target'] = $src;
					$optList['new_query'] = true;
				}
		}

		$Context = $ComponentContainer->Context[static::class];
		// $this->createContext($Context['self'], $Context['render'], $Context['data'], $Context['pattern']);
		$this->Load($options[static::class]);

		$json = [];
		$WorkData['render'] = $this->context['root']->nodes[0]->tokens;
		$WorkData['selector'] = 'data';
		return $WorkData;
	}


	public function FetchBinding($support)
	{
		global $db;
		$fetch = $support['dataset'];

		$opts = [
			'method'    =>  'WHERE `dataset` = \'' . $fetch . '\'',
			'condition' =>  'ORDER BY `when` DESC LIMIT 1'
			//'debug' => true
		];
		if (empty($support['tag']))
			$support['tag'] = '';

		$support['tag'] = mysqli_real_escape_string($db, $support['tag']);
		$opts['method'] .= ' AND `tag` = \'' . $support['tag'] . '\'';

		$binding = LoadObject('datamaps', $opts);

		if (empty($binding))
			return $this->FetchDatasetHeader(['dataset' => $fetch, 'composed' => false]);
		else
			return ['payload'   =>  json_decode($binding->data['binding'], true)];
	}

	// Normalize and generate options for select query: SELECT $src WHERE $PrimaryKey = $cid[]
	public static function GenerateSelectQueryForRemoteInstance($src, $cid, $options = [], $i = 0)
	{
		$PrimaryKey = $src::$profile['Accessor']['Primary'];
		if (is_array($cid) && count($cid) === count($src)) {
			$options[static::class][$src] = [
				'target' => $src,
				'method' => 'WHERE `' . $PrimaryKey . '` = ' . $cid[$i] . ' LIMIT 1'
			];
		} elseif (is_array($cid)) {
			$options[static::class][$src] = ['target' => $src, 'method' => 'WHERE `' . $PrimaryKey . '` = ' . $cid[0] . ' LIMIT 1'];
			// $PrimaryID = $cid[0];
		} elseif (!empty($cid) || $cid === 0) {
			$options[static::class][$src] = ['target' => $src, 'method' => 'WHERE `' . $PrimaryKey . '` = ' . $cid . ' LIMIT 1'];
			// $PrimaryID = $cid;
		} else {
			$options[static::class][$src] = ['target' => $src, 'method' => 'WHERE `' . $PrimaryKey . '` = NULL LIMIT 1'];
			// $PrimaryID = null;
		}
		return $options;
	}

	/// END DATA MAPPING FUNCTIONALITY


	/*
		function Create___Draft($support)
		{
			global $SupportPath;

			// $opts= [
			// 	'method'    =>  'WHERE `dataset` = \'component::' . static::class . '\'',
			// 	'condition' =>  'ORDER BY `when` DESC LIMIT 1'
			// 	//'debug' => true
			// ];
			// if(empty($support['tag']))
			// 	$support['tag']='';
			//
			// $support['tag'] = mysqli_real_escape_string($db, $support['tag']);
			// $opts['method'] .= ' AND `tag` = "settings" ';
			//
			// $settings_map = LoadObject('datamaps',$opts);



			$selector = 'selectedComponent()';
			$pattern = $SupportPath . '/templates/' . static::class . '.xml';
			if(!empty($support['pattern'])){
				if(ctype_alnum($support['pattern']) && preg_match('/\s/',$support['pattern']))
					$pattern = $SupportPath . '/templates/' . $support['pattern'];
				else exit('{"error": C08, "msg":"Provided template had illegal characters."}');
			}
			$null_root = new Smart(['pattern'=>$pattern]);
			$null_root->tag = !empty($this->ContainerTag) ? $this->ContainerTag : 'div';
			if(!empty($this->ContainerClasses))
				$null_root->classes = $this->ContainerClasses;
			if(!empty($this->ContainerAttributes))
				$null_root->attributes = $this->ContainerAttributes;

			$null_root->classes[]='Component';
			$null_root->classes[]=static::class;
			$null_root->attributes['data-component'] = static::class;

			$binding = $null_root->TemplateBinding[static::class];

			$source = array_keys($binding)[0];		// Get first table from template binding

			$accessor = $source::$profile['Accessor']['Primary'];
			$spawned = new $source($source);
			// $spawned->options['debug']=true;
			$spawned->create();
			$cid =$spawned->data[$accessor];
			$cid_formatted = is_string($cid) ? '"'.$cid.'"' : $cid;
			$spawned = LoadObject($source,[
				'condition'	=>	'WHERE '.$accessor.' = '.$cid_formatted
			]);

			// make sure CID is assigned component -sel fo someting

			// TODO: instead of loading, use the spawned. Tell options that it's prefetched
			// $options['prefetch'] = $spawned->data;     maybe how to do it
	        $options[static::class][$source] = [
				'target' => $source,
				'new_query' => true,
				'method' => 'WHERE `' . $accessor . '` = ' . $cid_formatted . ' LIMIT 1'
			];
		    $null_root->BindContext();

		    $Context = $null_root->context[static::class];
			$Component = new static::class();
		    $Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['pattern']);
		    $Component->Load($options[static::class]);

			if(!empty($support['_response_target']))
				$selector = $support['_response_target'];
			return ['render' => $null_root->render(), 'selector' => $selector];
		}
	*/
}
