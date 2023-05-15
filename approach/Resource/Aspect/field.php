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
	public static $label			= null;		// label for the field
	public static $type 			= null;		// type of the field
	public static $default			= null; 	// default value for the field
	public static $nullable			= null; 	// whether the field can be null
	public static $readonly			= null; 	// whether the field is read-only
	public static $required			= null; 	// whether the field is required
	public static $description		= null; 	// description of the field
	public static bool $is_accessor = false; 	// whether the field is an accessor
}
