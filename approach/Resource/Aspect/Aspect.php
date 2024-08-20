<?php

namespace Approach\Resource\Aspect;

use \Approach\Render;
use \Approach\Render\Node;
use \Approach\Render\Container;
use \Approach\Resource\Aspect\aspects;
use \Approach\Resource\Resource;
use Stringable;

class Aspect extends Container
{
	public readonly null|Render\Stream $parent;
	public Render\Stream $ancestor;

	final const container				= 0;
	final const location				= 1;
	final const operation				= 2;
	final const field					= 3;
	final const quality					= 4;
	final const quantity				= 5;
	final const map						= 6;
	final const identity				= 7;
	final const roles					= 8;
	final const state					= 9;

	const _index_map = [
		'container'				=> self::container,
		'location'				=> self::location,
		'operation'				=> self::operation,
		'field'					=> self::field,
		'quality'				=> self::quality,
		'quantity'				=> self::quantity,
		'map'					=> self::map,
		'identity'				=> self::identity,
		'roles'					=> self::roles,
		'state'					=> self::state,
	];

	const _case_map = [
		self::container			=> 'container',
		self::location			=> 'location',
		self::operation			=> 'operation',
		self::field				=> 'field',
		self::quality			=> 'quality',
		self::quantity			=> 'quantity',
		self::map				=> 'map',
		self::identity			=> 'identity',
		self::roles				=> 'roles',
		self::state				=> 'state',
	];


	public array $criteria = [
		self::container			=> null,
		self::location			=> null,
		self::operation			=> null,
		self::field				=> null,
		self::quality			=> null,
		self::quantity			=> null,
		self::map				=> null,
		self::identity			=> null,
		self::roles				=> null,
		self::state				=> null,
	];

	public static $type = self::container;

	// public function __construct($filters, $operator)
	public function __construct(
		string|\Stringable|int $type = null,
		int|Aspect|string|Stringable $content = null,	// The value. TODO: Use trait to get content out and make this $value
		&$parent = null,
		Container &$ancestor = null,
		array ...$nodes
	)
	{

		if ($parent === null)
		{
			$parent = &Node::$null;
		}

		// Get the class name, without the namespace
		$leaf = substr(static::class, strrpos(static::class, '\\') + 1);
		$leaf = $leaf === 'Aspect' ? 'container' : strtolower($leaf);

		$type = $type !== null ? static::match($type) : static::match($leaf);
		if ($type === false)
		{
			// Emite E_WARNING
			trigger_error('Invalid aspect type, defaulting to container', E_USER_WARNING);
			$type = self::container;
		}

		if ($ancestor === null)
		{
			$ancesotr = &Node::$null;
		}

		$this->content = $content;

		$this->parent = $parent;
		if ($ancestor === null)
			if ($this->parent !== null)
				$this->ancestor =  &$this->parent;
			else $this->ancestor = &$this;
		else $this->ancestor = &$ancestor;

		foreach ($nodes as $aspect)
		{
			if ($aspect instanceof self)
				$this->criteria[static::match($leaf)] = &$aspect;

			// Invalid aspect if we were unable to make an instance
			if (!$aspect instanceof self)
				throw new \Exception('Invalid aspect');

			//$this->aspects[$aspect->type->value][] = &$aspect;
		}
	}


	/* 
	 * cases() - Return an array of the enum's cases
	 * 
	 * @return array
	 * 
	 */
	public static function cases()
	{
		return array_values(static::_case_map);
	}

	/* 
	 * indices() - Return an array of the enum's indexes
	 * 
	 * @return array
	 * 
	 */
	public static function indices()
	{
		return static::_index_map;
	}

	/* 
	 * allowed() - Return a keyed dictionary of the enum's cases
	 * This is not static because child classes may have
	 * properties which whitelist or blacklist cases
	 * 
	 * @return array
	 * 
	 */
	public function allowed()
	{
		return static::_case_map;
	}

	/**
	 * match() - Return index if given a string, or key if given an int
	 * 
	 * Q: How can we allow an internal state so $value = myaspect::container is an aspect and match can be called on it?
	 * 
	 * @param string|int $case
	 * @return int|string|false
	 */

	public static function match(int|string|\Stringable $case)
	{
		if (is_int($case)) return static::_case_map[$case] 				?? false;
		else 				return static::_index_map[strtolower($case)] 	?? false;
	}

	/**
	 * manifest() - Generate Aspect classes for the given Resource
	 * 
	 * @param \Stringable|string|Resource $resource
	 * @return array
	 */

	// public static function manifest(null|\Stringable|string|Resource $resource = null)
	// {
	// 	// If no resource was given, use the context we are in
	// 	$namespace = static::class;

	// 	// Allow a resource to take precedence over the context as the
	// 	// generic Resource\Aspect\aspects can be used by any resource
	// 	// and has the primary taxonomy of aspects in its cases()
	// 	if($resource !== null)
	// 	{
	// 		// Derrive the namespace from the given resource's underlying class
	// 		$namespace = get_class($resource);
	// 		$namespace = substr($namespace, 0, strrpos($namespace, '\\'));
	// 	}

	// 	// Scan the namespace for Aspect classes matching this enum's cases
	// 	$aspects = [];
	// 	foreach (static::cases() as $case)
	// 	{
	// 		$aspects[] = static::define($case, $namespace);
	// 	}
	// 	return $aspects;
	// }

	/**
	 * Define aspect tree extending from this class
	 * Used by Resource via Aspect::manifest(myresource)
	 * 
	 * The main aspect *types* are:
	 * - location	- place in the installed protocols, MariaDB://myhost/mydatabase/mytable.myfield
	 * - field		- a field of a resource
	 * - operation	- a verb to perform on a resource, potentially with keyed arguments
	 * - quantity	- a number this resource relates to such as block sizes, inventory counts, etc
	 * - quality	- condition a resource can meet eg color, size, set of permissions, shipping route, etc
	 * - map		- a map from some scope to|from a resource or its aspects
	 * - identity	- a user, system, team, organization, etc.. entities that can instantiate a role
	 * - state		- a settable and trackable state of a resource such as 'logged_in', 'locked', 'active', etc
	 * - roles		- authorizations available to roles for this resource. eg. 'edit', 'view', 'delete', etc
	 * 
	 * Each aspect type may have its own subtypes, or not.
	 * This is left to library developers to interpret in their specific context.
	 * 
	 * location is either explicit or set by Resource::find()
	 * When using find(), a resource may use aspects in the following methods
	 * to resolve such locations. By default this applies to fields, considering data resources, 
	 * but may be used for other aspects as well. 
	 * 
	 * An example of non-field location is a resource that is a physical object
	 * and is resolved by intersecting a color quality and shape quality. e.g.:
	 * 
	 * $myresource = Project\Resource\Thing::find()
	 * 		->pick(Aspect::quality, qualities\color::red)		
	 * 				// References: /MyProject/Resource/SomeServer/Thing/Aspects/qualities/*.php
	 * 		->pick(Aspect::quality, qualities\shape::circle)
	 * 		->sort(Aspect::quality, quality::lighter)
	 * 				// References: /MyProject/Resource/SomeServer/Thing/Aspects/quality.php
	 * 		->sort(Aspect::quality, quality::larger)
	 * 		->sift(Aspect::roles, roles::view )
	 * 				// References: /MyProject/Resource/SomeServer/Thing/Aspects/roles.php
	 * 		->sift(Aspect::operation, operation::has_knob )
	 * 				// References: /MyProject/Resource/SomeServer/Thing/Aspects/operation.php
	 * 				// In reality you may be using \Approach\Resource\Aspect\* often
	 * 				// All references assume 'use' imported and named such classes;
	 * 				// either your project or Approach could be assigneed to "qualities", "quality, "roles"..
	 * 		->load();
	 * 
	 * But, more commonly in web development, a resource is a data resource.
	 * In this case, the location is a field of a table in a database. Still, one
	 * can add special cases to a generated aspect class to resolve a location
	 * using qualities to describe what to do with the field. 
	 * 
	 * A more general example of data usage:
	 * 
	 * $myresource = Project\Resource\Thing::find()
	 *		// use static roles of field to ensure the field is defined, but string is fine too
	 * 		->pick(Aspect::field, [ 'id', 'name', field::description ]) 
	 * 
	 * 		// a data-centric resource library will assume sort is over field types, probably..maybe
	 * 		->sort(field::name, quality::ascending) 
	 * 
	 * 		// this Namespace\quality.php has implimented sift() to switch(case) they type argument
	 * 		->sift(Aspect::quality, qualities\color::red)
	 * 
	 * 		->load();
	 * 
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

	/*	protected static function define(int $case, string $namespace)
	{
		// Becomes a dictionary of [ $target_namespace => $content ] to create
		$files_to_create = [];

		/* 
         * Aspect classes are generated in a Resource\..\MyResource\ namespace
		 * Extract the path component immediately following 'Resource\' from __NAMESPACE__
		 * This is the name of the Resource class and will match Scope::$proto[ 'my resource protocol' ]
		 */
	/*
		$proto = substr(__NAMESPACE__, strrpos(__NAMESPACE__, '\\') + 1);

		// Extract the remaining path components from __NAMESPACE__, starting after 'Resource\[active_resource]\'
		$type = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));
		$type = substr($type, strrpos($type, '\\') + 1);

		// Get FQCN of the Resource class one namespace level up from the current static::class
		// Will be the same as __NAMESPACE__ if the Resource class is in the root namespace
		$resource = substr(static::class, 0, strrpos(static::class, '\\'));
		$p = parent::class;


		// If the aspect generator is defined, call it to get the files to create
		if(static::$definer[$case] instanceof \Closure || is_callable(static::$definer[$case])){
			$files_to_create = static::$definer[$case]($type, $resource); // $namespace);
		}
		elseif(is_a($p, Aspect::class, true)){
			if($p::$definer[$case] instanceof \Closure || is_callable($p::$definer[$case]))
				$files_to_create = $p::$definer[$case]($type, $resource); // $namespace);			
		}
		elseif(self::$definer[$case] instanceof \Closure || is_callable(self::$definer[$case])){
			$files_to_create = self::$definer[$case]($type, $resource); // $namespace);
		}
	
		// Otherwise, this aspect does not need to be generated
		return $files_to_create;
	}
	*/
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

class operation extends Aspect
{
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

class quantity extends Aspect
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

class map extends Aspect
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
 * aspects implement ArrayObject through Container, so this is a way to append authorization information to an aspect according to a role key and child node permissions key 
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

class authorization extends Aspect
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
