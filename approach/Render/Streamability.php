<?php

namespace Approach\Render;

use \Approach\help\resource;
use \Approach\nullstate;
use \Stringable;

trait Streamability 
{
    private array $_labeled_nodes = [];		
	private array $_node_labels = [];		// string keys 
	
	public function &toArray()
	{
        return [
            $this->getNodeProperties(),
			...$this->nodes->getNodeProperties() 
		];
	}
    
	public function __set(mixed $label, mixed $val)
	{
        $this->offsetSet($label, $val);
	}
    
	public function __get(mixed $label)
	{
        return $this->offsetGet($label);
	}
    
	public function getNodeProperties()
	{
        return [
            ...$this->getHeadProperties(),
			...$this->getCorpusProperties(),
			...$this->getTailProperties()
		];
	}
    
	protected function getHeadProperties(): array
	{
        return [];
	}
	protected function getCorpusProperties(): array
	{
        return [
            'content'	=> $this->content		/**<	TODO: make enum labels	>*/
		];
	}
	protected function getTailProperties(): array
	{
        return [];
	}
    
	public function offsetExists($label): bool|nullstate
	{
        if (is_int($label))	return isset($this->nodes[$label]);
		else return
			isset($this->_labeled_nodes[$label])
			?(
				isset($this->nodes[$this->_labeled_nodes[$label]])
				?
					true
				:	false //nullstate::undefined
			)
			:	false; //nullstate::undeclared;
	}

	public function offsetGet(mixed $label): mixed
	{
		if (is_int($label))	return

			// If the label is actually a direct offset to the nodes array, return it
			$this->getLabeledNode($label)
			??
			// If a provided index is not in the array, return nullstate::undeclared
			nullstate::undeclared;

		$label_index = $this->getNodeLabelIndex($label);
		// echo 'label ';
		// var_dump($label);
		// echo PHP_EOL . 'index ';
		// var_dump($label_index);

		// echo PHP_EOL . 'labeled nodes ';
		// var_dump($this->_labeled_nodes);

		// echo PHP_EOL . 'node labels ';
		// var_dump($this->_node_labels);

		return 
			// If the label exists
			$label_index !== nullstate::undeclared
			?
				// If the label points to an existing node                
				$this->getLabeledNode( $label_index )

			:	// Or else, the label was never declared	
				nullstate::undefined;
	}

	public function offsetSet(mixed $label, mixed $value): void
	{
		if ($label === null){
			$this->nodes[] = $value;
			return;
		}

		$label_index = $this->getNodeLabelIndex($label);
		
		if($label_index !== nullstate::undeclared && $this->getLabeledNode($label_index) !== nullstate::undefined)
		{
			// echo 'getting label index... found: '.$label_index.PHP_EOL;
			$selected = $this->getLabeledNode( $label_index );
			// echo 'getting labeled node... found: ' . get_class($selected) . PHP_EOL;

			$selected = &$value;
			// echo 'setting labeled node to a ' . get_class($value) . PHP_EOL;
		}
		else
		{
			// echo 'getting label index... undeclared. adding... '  . PHP_EOL;

			$this->nodes[] = $value;								// Actual Nodes, Not all labels
			$node_index = count($this->_node_labels);				// Index of the soon to be added label
			$this->_node_labels[] = $label;							// Push the label to the label array
			$this->_labeled_nodes[$node_index] = end($this->nodes);	// Label Index Storage
		}
	}

	protected function getLabeledNode(int $label_index)
	{
		return
			$this->_labeled_nodes[$label_index]
			??
			nullstate::undefined;
	}

	/**
	 * Returns an index that works with $this->_labeled_node[...] to find a node you labeled
	 * 
	 * @param string $label
	 * @return int|null
	 */
	protected function getNodeLabelIndex(string|Stringable $label)
	{
		$offset = array_search($label, $this->_node_labels);
		// echo PHP_EOL.'looking for label index.. found: '.$offset.PHP_EOL;
		return $offset !== false ?
			$offset
		:	nullstate::undeclared
		;
	}

	public function offsetUnset(mixed $label): void
	{
		if (is_int($label) && isset( $this->nodes[$label] )){
			unset($this->nodes[$label]);
			return;
		}

		if(isset($this?->nodes[$this?->_labeled_nodes[$label]]))
			unset($this->nodes[$this->_labeled_nodes[$label]]);
		
		return;
	}
}

// abstract class selectable{
// 	const index = 0;
// 	const pick = 1;	 // properties selected by name
// 	const sift = 2;	 // various ways of describing rejection criteria as arrays to unpack for comparison or registered operations
// 						// in URL form: proto://root/path[field:20..30,field2:~"regex",field3:"apple|orange|banana"]
// 	const weigh = 3;	// each sift may assign a weight to passing nodes
// 	const operate = 4;  // operations may be current_namespace/method(), $this->method() or static::class::method($this,...$params)
// 	const property = 5; // an embedded property of the syntax node.property
// 	const accessor = 6; // a property of the node that changes $node to $node = static::access($node,$accessor)
// }
//
// use \Approach\Scope;
// use \Approach\context;
//     
// trait Streamable
// {
//     /**
//      * This trait is used to implement the StreamWrapper interface
//      * This trait may access Scope, and Scope may access this trait
//      * in order to implement the StreamWrapper interface.
//      * 
//      * array $root_streams = Scope::$Active->context[ context::stream ][ static::class ]
//      * 
//      * Root streams are inferred from the current scope's stream contexts
//      * Each entry in the array is Node class that uses this trait
//      * 
//      * Some nodes in the tree may not be streamable, but they may be
//      * able to be streamed to. This is because the stream wrapper
//      * interface is implemented by the node class, not the node instance.
//      * 
//      * Nodes along the path which are Streamable align with path segments
//      * in a URI based on the following rules:
//      * 
//      * 1. If the node is a root node, it is the first segment in the URI
//      * 2. Each node exposes Node->_labeled_nodes and Node->_node_labels
//      *      - Streamability->getLabeledNode($label) returns the node at $Node->_labeled_nodes[$label]
//      *      - $Node[$label] thus matches the node at $Node->_labeled_nodes[$label]
//      *      - If the node has a label, it is in the next segment in the URI
//      *      - Thus, $some_root[$label][$label2][$label3] is the URI Node://root/$label/$label2/$label3
//      *          - if $label2 is not a label of $root[$label], and it is an integer, it is the index of the node
//      *          - if $label2 is not a label of $root[$label], and it is a string, it is the name of the node
//      * 3 If a '.' is found after the root node, it is a property of the node(s) in the path
//      *     - e.g. Node://myroot/special_node.property
//      *     - e.g. Node://myroot/special_node.property_node/child_of_property.another_property
//      *     - if this fails, try treating the . as a / and continue
//      * 4. If a '?' is found after the root node they are either
//      *      - properties to override in the resolved node
//      *      - function parameters, if a fuunction was selected: Type://root/label.myfunction()?param1=value1&param2=value2
//      * 5. Each Node class may implement a function to handle it's label and path subcomponents /path/to[optional];optional;optional/node
//      *      - Such handling must be done at the per-label level
//      *      - A given node instance will always be selected and equal $this while traversing the path
//      */
//     
//     use Streamability;
//     public resource $context;
//     /**
//      * nullstate is an enum that can be one of the following values:
//      * null, defined, undeclared, undefined, undefined_type, 
//      * uninitialized, empty, stalled, virtual, ambiguous, 
//      * becomingnull, nolongernull, notnull
//      * 
//      * @var nullstate $state 
//     */
//     public nullstate $state;
//
//     /* Implement remaining StreamWrapper methods based on node labels */
//     public function stream_open(string $path, string $mode, int $options, string &$opened_path): bool
//     {
//         $this->state = nullstate::uninitialized;
//
//         /* TODO: implement context::get() // returns the below array */
//         Scope::$Active->context[context::stream][static::class] = Scope::$Active->context[context::stream][static::class] ?? new Container;
//         $protocol = &Scope::$Active->context[context::stream][static::class];
//
//         /*  To become
//             $protocol = context::stream->get( type: static::class );
//         */
//
//         // Check for the root type://root in $protocol[root]
//         
//         $path = explode(
//             trim(
//                 $path, 
//                 '/'
//             ),
//             '/'
//         );
//         $label = array_shift($path);
//
//         if (isset($protocol[$label])) {
//             $this->context = $protocol[$label];
//             $this->state = nullstate::undeclared;
//         }
//
//         // Resolve the path with ->resolvePath($path)
//         $found = $this->resolvePath($path);
//
//         if (isset($protocol[$path])) {
//             $this->context = $protocol[$path];
//             $this->state = nullstate::defined;
//             return true;
//         }
//
//         $protocol[] = $this;
//         Scope::$streams[$this->_render_id] = [
//             'wrapper_data' => $this,
//             'wrapper_type' => static::class,
//             'mode' => $mode,
//             'options' => $options,
//             'opened_path' => &$opened_path,
//         ];
//
//         $context = Scope::$Active->context[ context::stream ][ static::class ];
//         $this->context = new resource($this);
//
//         $this->state = nullstate::defined;
//         return true;
//     }
//
//     /**
//      * Resolves a path to a node recursively
//      * relative to its ->$nodes[] and ->$node_labels[]
//      * In line with above URI rules
//      */
//     public function resolvePath(array $path, &$root = null)
//     {
//         // Allow relative paths
//         $node = $root ?? $this;
//         if(empty($path)) return $node;
//
//         // Get the next label in the path
//         $label = array_shift($path);
//         $info = [];
//
//         
//         $info = $this::parse_label($label);
//         
//         // ...
//     }
//     
//     /**
//      * Save metadata about the path component.
//      * Process dots, subcompontents, array brackets, etc. using strpos, array_map, etc.
//      * 
//      * e.g. $label = 'mylabel[0].sublabel' becomes 
//      *      $label = 'mylabel';
//      *      $info['range'] = [0];   
//      *      $info['range_type'] = [ 'index|pick|sift|weigh|operate|property|accessor',.. ]; // anything in the above enum
//      * 
//      * Allows $node[property:0..10].property2 to be used to indicate:
//      *   - foreach $this->nodes
//      *      - if $node->property < 0 || $node->property > 10 then skip
//      *      - yield output or select $node->property2 for each node
//      * 
//      *  Handle dots, brackets, etc. in the label recursively
//      */
//     public static function parse_label($label){
//         $info = [];
//         
//         $L0_delimiters = [
//             '@',            // accessor
//             ']', 
//             '[',             // sift and picks
//              '.',           // embedded property
//              ';'            // path subcomponent, may segment userdata or custom handling
//         ];
//
//         // URL-safe del
//             
//
//         return $info;
//     }
// }
//
//
