<?php

namespace Approach\Render;
use \Approach\nullstate as nullstate;
use \Approach\Render\Stream;
use \Stringable;


class Node extends \ArrayObject implements Stream
{
	private array $_labeled_nodes = [];	/**<	Store this.nodes[index] at _labeled_nodes[ label_index ]	>*/
	private array $_node_labels = [];	/**<	Simulate good enum labels at each label_index	>*/
	
	public $nodes = [];
	public static Node $null;
	
	use Node\Properties;
	use Streamability;

	public function __construct(public null|string|Stringable|Stream $content = null, public bool $prerender = false)
	{
		// $this->set_render_id();
	}

	# an initializer for the statics
	public static function __static_init()
	{
		self::$null = new Node();
	}

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
