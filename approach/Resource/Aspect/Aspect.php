<?php

namespace Approach\Resource\Aspect;

use \Approach\Render;
use \Approach\Render\Node;
use \Approach\Resource\Aspect\aspects;

class Aspect extends Node
{
	public readonly ?Render\Stream $parent;
	public ?Render\Stream $ancestor;

	// public function __construct($filters, $operator)
	public function __construct(public aspects $type, $content=null, ?array $aspects = array(), $parent = null, ?Node $ancestor = null)
	{
		if($parent === null){
			$parent =& Node::$null;
		}

		if($ancestor === null){
			$ancesotr =& Node::$null;
		}

		$this->content = $content;

		$this->parent = $parent;
		$this->ancestor = $ancestor == Node::$null ? $this : $ancestor;
		foreach ($aspects as $aspect)
		{
			// Convert from enum to instance
			if($aspect instanceof aspects)
			{
				$aspect = new self($aspect, $this, ancestor: $this->ancestor);
				$this->nodes[] = $aspect;
			}

			// Convert from parameter array to instance
			if(is_array($aspect))
			{
				$aspect = new self(...$aspect, parent: $this, ancestor: $this->ancestor);
				$this->nodes[] = $aspect;
			}

			// No conversion necessary
			elseif($aspect instanceof self)
				$this->nodes[] = &$aspect;

			// Invalid aspect if we were unable to make an instance
			if(!$aspect instanceof self)
				throw new \Exception('Invalid aspect');

			$this->aspects[$aspect->type->value][] = &$aspect;
		}
	}

	/**
	 * Define aspect tree extending from this class
	 * Used by Resource via aspects::define() to describe the selectivity for loading a resource
	 * 
	 * The main aspect *types* are:
	 * - location
	 * - field
	 * - operation
	 * - quality
	 * - quantity
	 * - map
	 * - state
	 * - authorization
	 * 
	 * The main aspect primarily used to describe the location of a resource..
	 * - where
	 * 
	 * ..and the fields modifiers of a Resource\Resource
	 * - pick		// pick the fields to be included in the results
	 * - sort		// sort the results by the given aspect tree
	 * - weigh		// weights to augment the sort
	 * - sift		// prevent certain aspect matches from inclusion in the results
	 * - divide		// divide the resource into groups
	 * - pipe		// send the results forward to a Service or through some map aspect
	 * 
	 * @param array $aspects
	 * @return Aspect
	 * 
	 */

	public function define()
	{
		/* 
         * All aspect classes are generated in a Resource\MyResource namespace
		 * Extract the path component immediately following 'Resource\' from __NAMESPACE__
		 * This is the name of the Resource class and will match Scope::$proto[ 'my resource protocol' ]
		 */
		$proto = substr(__NAMESPACE__, strrpos(__NAMESPACE__, '\\') + 1);

		// Extract the remaining path components from __NAMESPACE__, starting after 'Resource\[active_resource]\'
		$type = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));
		$type = substr($type, strrpos($type, '\\') + 1);

		// Get FQCN of the Resource class one namespace level up from the current static::class
		// Will be the same as __NAMESPACE__ if the Resource class is in the root namespace
		$resource = substr(static::class, 0, strrpos(static::class, '\\'));

		// Use aspects::manifest on $resource to get the manifest of the Resource class
		$manifest = aspects::manifest($resource);

		foreach($manifest as $kind => $aspect)
		{
			$k = new aspects();
			switch($kind)
			{
				case aspects::location->value: $k= aspects::location;
				case aspects::operation->value: $k= aspects::operation;
				case aspects::field->value: $k= aspects::field;
				case aspects::quality->value: $k= aspects::quality;
				case aspects::quantity->value: $k= aspects::quantity;
				case aspects::map->value: $k= aspects::map;
				case aspects::state->value: $k= aspects::state;
				case aspects::authorization->value: $k= aspects::authorization;
			}
			// Build a tree of Aspect objects
			$this->nodes[] = new Aspect(
				type: $k, 
				content: $proto . '://'.$type.'/'.''/* TODO: PUT ClassName\Fields::Something dynamically */, 
				parent: $this, 
				ancestor: $this->ancestor ?? $this->parent ?? $this
			);
		}

		/*/ 
		// Build a tree of Aspect objects
		$this->nodes[] = new Aspect(
			type: aspects::location, 
			content: $proto . '://'.$type.'/', 
			parent: $this, 
			ancestor: $this->ancestor ?? $this->parent ?? $this
		);
		/**/
	}
}

// Path: approach\Resource\aspects.php

/*
	Describe locations, fields, operations, qualities, quantities, maps, states and authoritizations
	To be used in the manifest of a resource
*/


/**
 * operation aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Operation
 * @category	Method
 * 
 */

 class operation{
	public static $method;										// method to be used to access the resource
	public static $parameters;									// parameters that the operation accepts
	public static $accepts;										// media type(s) that the operation can consume
	public static $provides;									// media type(s) that the operation can produce
	public static $requires;									// resources that are required to access the resource
	public static $errors;										// errors that the operation can return
	public static $signature;									// headers of the operation
	public static $description;									// description of the operation

	public static bool $is_create; 								// whether the operation is a create operation
	public static bool $is_read; 								// whether the operation is a read operation
	public static bool $is_update; 								// whether the operation is an update operation
	public static bool $is_delete; 								// whether the operation is a delete operation
	public static bool $is_list; 								// whether the operation is a list operation
	public static bool $is_search; 								// whether the operation is a search operation
	public static bool $is_action; 								// whether the operation is an action operation
	public static bool $is_function; 							// whether the operation is a function operation
}

/**
 * quality aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Quality
 * @category	Category
 * @category	Tag
 * @category	Keyword
 * 	
 */

 class quality
{
	public static $label;										// label for the quality
	public static $description;									// description of the quality
	public static $keywords;									// keywords for the quality
	public static $children;									// children qualities of the quality
	public static $related;										// related qualities of the quality
	public static $type;										// the type of the quality
	public static $state;										// the present state of the quality
}

/**
 * quantity aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Quantity
 * @category	Count
 * @category	Amount
 * 
 */

 class quantity
{
	public static $label;										// label for the quantity
	public static $description;									// description of the quantity
	public static $values;										// valid value of the quantity
	public static $ranges;										// ranges of the quantity
	public static $units;										// unit of the quantity
	public static $unit_labels;									// label for the unit of the quantity
	public static $min;											// minimum value of the quantity
	public static $max;											// maximum value of the quantity
	public static $step;										// step of the quantity
	public static $precision;									// precision of the quantity
}

/**
 * mapping aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Mapping
 * @category	Conversion
 * @category	Transformation
 * 
 */

class map
{
	public static $type;										// type of the mapping (Token map, Settings map, Service map, etc.)
	public static $label;										// label for the mapping
	public static $tag;											// tag augmenting the label
	public static $version;										// version of the mapping
	public static $last_modified;								// last modified date of the mapping
	public static $description;									// description of the mapping
	public static $from;										// source of the mapping
	public static $to;											// destination of the mapping
	public static $known_callers;								// known callers of the mapping
	public static $previous;									// previous version of the mapping
	public static $map;											// the map structure
}

/**
 * Authorization aspect class
 * appended to an aspect to indicate that the aspect must be authorized according to the authorization aspect's realms, roles, and permissions
 * aspects implement ArrayObject through Node, so this is a valid way to append authorization information to an aspect according to a role key and child node permissions key 
 * (e.g.)
 * $permission = new Resource\Authorization(Aspect $aspect, $degree);	// degree denotes read, write, update, delete, create, list, search, action, admin or browse
 * 																		// $aspect is the aspect to which the permission applies
 * 																		// complex permissions can be created by nesting permissions in the $aspect's $this->nodes
 * MyResource::authorizations = new Resource\Authorization(...)
 * MyResource::authorizations['role'] = $permission;
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Authorization
 * @category	Realm
 * @category	Role
 * @category	Permission
 * 
 */

 class authorization
{
	public static $label;										// label for the authorization
	public static $description;									// description of the authorization
	public static $realms;										// realms of the authorization
	public static $roles;										// roles of the authorization
	public static $permissions;									// permissions of the authorization
	public static $degree;										// degree of the authorization
	public static $read;										// read permission of the authorization
	public static $write;										// write permission of the authorization
	public static $update;										// update permission of the authorization
	public static $delete;										// delete permission of the authorization
	public static $create;										// create permission of the authorization
	public static $list;										// list permission of the authorization
	public static $search;										// search permission of the authorization
	public static $action;										// action permission of the authorization
	public static $admin;										// admin permission of the authorization
	public static $browse;										// browse permission of the authorization
}
