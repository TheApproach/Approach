<?php

namespace Approach\Resource\Aspect;

use \Approach\Resource\Resource;

/**
 * aspects enum - defines the types of aspects Resource classes can have
 *				- defines the manifest() method for generating Aspect classes
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Location
 * @category	URI
 * 
 */

class discover extends \Approach\Render\Node
{
	use \Approach\Resource\discoverability;

	final const container				= 0;
	final const location				= 1;
	final const operation				= 2;
	final const field					= 3;
	final const quality					= 4;
	final const quantity				= 5;
	final const map						= 6;
	final const identity				= 7;
	final const access					= 8;
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
		'access'				=> self::access,
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
		self::access			=> 'access',
		self::state				=> 'state',
	];

	// Any value to this being an instance property, instead of a static property?
	public static $generator = [
		self::container				=> null,
		self::location				=> null,
		self::operation				=> null,
		self::field					=> null,
		self::quality				=> null,
		self::quantity				=> null,
		self::map					=> null,
		self::identity				=> null,
		self::access				=> null,
		self::state					=> null,
	];

	/* 
	 * cases() - Return an array of the enum's cases
	 * 
	 * @return array
	 * 
	 */
	public static function cases(){
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
	public function allowed(){
		return static::_case_map;
	}

	/**
	 * match() - Return index if given a string, or key if given an int
	 * 
	 * Q: How can we allow an internal state so $value = myaspect::container is an aspect and match can be called on it?
	 * A: What if we 
	 * 
	 * @param string|int $case
	 * @return int|string|false
	 */

	public static function match(int|string|\Stringable $case){
		if(is_string($case) || ($case instanceof \Stringable))
			 return static::_index_map[	strtolower($case) ] ?? false;
		else return static::_case_map[ $case ] ?? false;
	}
}
