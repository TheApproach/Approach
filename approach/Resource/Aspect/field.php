<?php

namespace Approach\Resource\Aspect;

use \Approach\Render\Node;

/**
 * field aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Field
 * @category	Property
 * 
 */

class field extends Aspect{
	public static $label		= null;							// label for the field
	public static $default		= null; 						// default value for the field
	public static $nullable		= null; 						// whether the field can be null
	public static $readonly		= null; 						// whether the field is read-only
	public static $required		= null; 						// whether the field is required
	public static $description	= null; 						// description of the field
	public static bool $is_identifier; 							// whether the field is an identifier
	public static bool $is_resolver; 							// whether the field is a resolver

	public function __construct(
		public aspects $type=aspects::field, 
		$label,
		public $content=null, 
		?array ...$aspects, 
		$parent=Node::$null, 
		Node $ancestor = Node::$null
	)
	{
		$this->label = $label ?? $content;
		parent::__construct($type, $content, ...$aspects, $parent, $ancestor);
		// $this->is_identifier = $this->type == aspects::identifier;
		// $this->is_resolver = $this->type == aspects::resolver;
	}
}
