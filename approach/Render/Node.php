<?php

/**
 * 
 * This is the Node class, which is the base class for all Approach\Render objects.
 * 
 * @package Approach
 * @subpackage Render
 * @version 2.0.-1 beta
 * 
 * @license Apache 2.0
 * @since	2023-02-04
 * @see	https://orchetrationsyndicate.com/Approach/Render/Node
 * 
 * 
 * @property null|string|Stringable|Stream $content The content of the Node object.
 * @property bool $prerender A boolean value that determines whether or not the Node object should be prerendered.
 * @property array $nodes An array of Node objects that are children of the Node object.
 * @property array $_labeled_nodes An array of Node objects that are children of the Node object.
 * @property array $_node_labels An array of Node objects that are children of the Node object.
 * 
 * @method static Node __set_state(array $properties) This creates a new Node object with properties set from an array.
 * @method Node __construct(null|string|Stringable|Stream $content = null, bool $prerender = false) This is the constructor for the Node class.
 * @method void static __static_init() A static initializer that instantiates the null Node, for use in comparisons.
 * @method Node __set_state(array $properties) This creates a new Node object with properties set from an array.
 * @static @method Node|nullstate getById(int $id) This function returns a Node object with a specific ID.
 * 
 * @uses Approach\Render\Node\Properties
 * @uses Approach\Render\Node\Streamability
 */

namespace Approach\Render;
use \Approach\nullstate as nullstate;
use \Approach\Render\Stream;
use Approach\Scope;
use \Stringable;

/*
	TODO: 
		- Specialize FatNode out of Node
			- add parent and ancestor mechanic to FatNode
				- replace downstream references to Node using __render_id with a reference to FatNode
				- replace downstream Node classes using a $parent to extend FatNode
				- replace downstream Node classes using an $ancestor to extend FatNode
			- add a $hyperlink property to FatNode
				- hyperlinks are a special type of node that can be used to create links to other nodes
				- hyperlinks are not rendered by default
				- hyperlinks are similar to <a> tags, but they are not HTML-specific,
				  they can be used to create graph-like structures between nodes
				- Approach uses this to create the Resource Protocol URLs
				  MariaDB://localhost/MyDatabase/MyTable[MyColumn,MyColumn2:0..10,MyColumn3]
				  MyAPI://api.example.com/MyEndpoint/Product.image_list@Gallery/Image[src:0..10]
				  shared://myproject.share.corp/support/patterns/MyPattern.xml
			-

*/






class Node extends Container
{
	// private array $_labeled_nodes = [];	
	// private array $_node_labels = [];	

	/**
	 * An array of Nodes that are linked to $this Node.
	 * Generally, children of $this - unless using a specialized climb.
	 * Occassionally, elements of $nodes[] may link to other nodes in the tree structure or
	 * treat $nodes[] as siblings, ancestors, hashes, etc.
	 * 
	 * @var Node[] $nodes 
	 */
	
	public $nodes = [];
	public static Node|null $null;

	use Node\Properties;
	use Streamability;

	public function __construct(public $content = null, public bool $prerender = false )
	{
        $this->set_render_id();
	}

	# an initializer for the statics
	/**
	 * A static initializer that instantiates the null Node, for use in comparisons.
	 */
	public static function __static_init()
	{
		self::$null = new Node();
	}

	/**
	 * This creates a new Node object with properties set from an array.
	 * 
	 * @param array properties An array of properties that need to be set for the Node object.
	 * 
	 * @return Node A Node object.
	 */
	public static function __set_state(array $properties): Node
	{
		$node = new Node();
		if(isset($properties['content']))
			$node->content = $properties['content'];
		if(isset($properties['prerender']))
			$node->prerender = $properties['prerender'];
		if(isset($properties['nodes']))
			$node->nodes = $properties['nodes'];		
		if(isset($properties['_render_id']))
			$node->_render_id = $properties['_render_id'];
		return $node;
	}

	/**
	 * This recursively searches for a node with a specific ID within a tree structure.
	 * 
	 * @param &$root This is a reference to the root node of a tree structure.
	 * @param $_render_id _render_id is a parameter that represents the unique identifier of a node in a
	 * tree structure. The function is designed to search for a node with a specific _render_id and return
	 * it if found.
	 * 
	 * @return Node|nullstate either a Node object or nullstate::null (which is likely a null value).
	 */
	public static function GetById(&$root, $_render_id): Node|nullstate
	{
		if ($root->_render_id == $_render_id) return $root;

		foreach ($root->children as $child) {
			$result = self::GetById($child, $_render_id);

			if ($result instanceof self) {
				if ($result->_render_id == $_render_id) return $result;
			}
		}

		return nullstate::null;
	}
}
// Node::__static_init() called by Scope::__static_init()