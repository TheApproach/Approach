<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Render\Container;
use \Approach\Render\Node;
use \Approach\Render\Node\Keyed;
use \Approach\nullstate;

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

	class field extends Container{
	/*
	The maps will be updated by child classes with additional constants, but will always include
	the above constants, with the same values, in the same order.
	*/
	
	const _index_map		= 0;
	const _case_map			= 1;
	const label				= 2;
	const type				= 3;
	const default			= 4;
	const source_type		= 5;
	const source_default	= 6;
	const nullable			= 7;
	const description		= 8;
	const constraint		= 9;
	const accessor			= 10;
	const reference_to		= 11;
	const primary_accessor	= 12;

	const _approach_field_profile_ = [
		self::_index_map		=> [],
		self::_case_map			=> [],
		self::label				=> [],
		self::type				=> [],
		self::default			=> [],
		self::source_type		=> [],
		self::source_default	=> [],
		self::nullable			=> [],
		self::description		=> [],
		self::constraint		=> [],
		self::accessor			=> [],
		self::reference_to		=> [],
		self::primary_accessor	=> [],
	];
	

	/* 
	 * cases() - Return an array of the enum's cases
	 * 
	 * @return array
	 * 
	 */
	public static function cases()
	{
		return array_values(static::_approach_field_profile_[self::_case_map]);
	}

	/* 
	 * indices() - Return an array of the enum's indexes
	 * 
	 * @return array
	 * 
	 */
	public static function indices()
	{
		return static::_approach_field_profile_[self::_index_map];
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
		return static::_approach_field_profile_[self::_case_map];
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
		if (is_int($case)) return static::_approach_field_profile_[self::_case_map][$case] 				?? false;
		else 				return static::_approach_field_profile_[self::_index_map][strtolower($case)] 	?? false;
	}

	public static function getOrdinalByName(string|\Stringable $case = null){
        return static::_approach_field_profile_[self::_index_map][$case] ?? null;
    }
	public static function getNameByOrdinal(int $case = null){
		return static::_approach_field_profile_[self::_case_map][$case] ?? null;
	}
	// public static function match($case){...} 	// inherited, accepts Ordinal or Name, returns the other

    public static function getType($case = null){
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::type][ $case ];
    }

	public static function getDefault($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::default][ $case ];
	}

	public static function getSourceType($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::source_type][ $case ];
	}

	public static function getSourceDefault($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::source_default][ $case ];
	}

	public static function isNullable($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::nullable][ $case ];
	}

	public static function getConstraint($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::constraint][ $case ];
	}

	public static function getDescription($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}
		return static::_approach_field_profile_[self::description][ $case ];
	}

	public static function isAccessor($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}

		return in_array($case, static::_approach_field_profile_[self::accessor]);
	}

	public static function getAccessors()
	{
		$accessors = [];
		foreach (static::_approach_field_profile_[self::accessor] as $accessor) {
			$accessors[static::match($accessor)] = $accessor;
		}
		return $accessors;
	}

	public static function getPrimaryAccessor()
	{
		return static::_approach_field_profile_[self::primary_accessor];
	}

	public static function getReferenceByAccessor($case = null)
	{
		if( !is_int($case) ){
			$case = static::match($case);
		}
		if( $case === null ){
			return nullstate::undeclared;
		}

		/**
		 * Describes the relationship between a field and another field.
		 * Often this in another Resouce, such as a foreign key.
		 * 
		 * @todo: Migrate all below into the qualities aspect. 
		 * 		  Each field-constraint is a quality of a MariaDB\Table class.
		 * 			- Effects Component::Save() and Resource::save() strongly
		 * 			- Effects Component::Delete() and Resource::delete() strongly
		 * 			- Should not effect Component::Load(), Resource::load(), Resource::push(), or Resource::pull() at all
		 * 				- pushes occur atomized after save disambiguates the relationships
		 * 				- pushes are on primary accessors in the standard case
		 */

		$relationships = [];
		foreach(static::_approach_field_profile_[self::reference_to] as $relational_link){
			foreach($relational_link as $accessor => $reference){
				if( $accessor === $case ){
					$relationships[] = $relational_link;
					continue;
				}
			}
		}

		return $relationships;
	}

	public static function rankRelationInto($target)
	{
		$related = 0;
		foreach (static::_approach_field_profile_[self::reference_to] as $relational_link) {
			foreach ($relational_link as $accessor => $reference) {
				$match = false;
				foreach($target as $k => $v){
					if ($reference[$k] === $v) {
						$match = true;
					}
					else{
						$match = false;
						break;
					}
				}
				if($match){
					$related++;
				}
			}
		}
		return $related;
	}


	public static function rankRelationFrom($target)
	{
		$related = 0;
		foreach ($target::reference_to as $relational_link) {
			foreach ($relational_link as $accessor => $reference) {
				$resource_match = false;
				$field_match = false;
				foreach($reference as $k => $v){
					if($k == 'resource' && $v == static::class){
						$resource_match = true;
					}
					elseif($k == 'field' && $v == $accessor){
						$field_match = true;
					}
				}
				if ($resource_match && $field_match) {
					$related++;
				}
			}
		}
		return $related;
	}

	public static function getRelationInto($target)
	{
		$relationship = [];
		foreach (static::_approach_field_profile_[self::reference_to] as $relational_link) {
			foreach ($relational_link as $accessor => $reference) {
				if ($reference['target'] === $target) {
					$relationship[] = $relational_link;
					continue;
				}
			}
		}
		return empty($relationship) ? false : $relationship;
	}

	public static function getRelationFrom($target)
	{
		$relationship = [];
		foreach ($target::reference_to as $relational_link) {
			foreach ($relational_link as $accessor => $reference) {
				if ($reference['target'] === static::class) {
					$relationship[] = $relational_link;
					continue;
				}
			}
		}
		return empty($relationship) ? false : $relationship;
	}

	public static function getRelationshipWith($target)
	{
		return [
			'into' => static::getRelationInto($target),
			'from' => static::getRelationFrom($target),
		];
	}

	public function render()
	{
		$render = new Node;
		
		foreach(static::cases() as $field){
			$pair = new Keyed( name: $field );
			$pair->associative_phrase = ':'.PHP_EOL;
			$pair->encapsulating_phrase = '';
			$pair->chaining_phrase = PHP_EOL.PHP_EOL;

			// Collect the properties of the aspect
			$info = static::getProfile($field);
			
			// Pretty print the properties as a JSON string
			$pair->content = json_encode($info, JSON_PRETTY_PRINT);
		}
		
		return $render->render();
	}

	public function stream()
	{
		$render = new Node;

		foreach (static::cases() as $field) {
			$pair = new Keyed(name: $field);
			$pair->associative_phrase = ':' . PHP_EOL;
			$pair->encapsulating_phrase = '';
			$pair->chaining_phrase = PHP_EOL . PHP_EOL;

			// Collect the properties of the aspect
			$info = static::getProfile($field);

			// Pretty print the properties as a JSON string
			$pair->content = json_encode($info, JSON_PRETTY_PRINT);
		}
		foreach ($render->stream() as $r){
			yield $r;
		}
	}

	public static function getProfileProperties($which = null)
	{
		$valid = [
			'label',
			'type',
			'default',
			'source_type',
			'source_default',
			'nullable',
			'description',
			'accessor',
			'reference_to',
			'primary_accessor',
		];

		if($which == null){
			return [
				'label'				=>	self::label,
				'type'				=>	self::type,
				'default'			=>	self::default,
				'source_type'		=>	self::source_type,
				'source_default'	=>	self::source_default,
				'nullable'			=>	self::nullable,
				'description'		=>	self::description,
				'accessor'			=>	self::accessor,
				'reference_to'		=>	self::reference_to,
				'primary_accessor'	=>	self::primary_accessor
			];
		}
		elseif(is_string($which) || $which instanceof \Stringable){
			return match($which){
				'label'				=>	self::label,
				'type'				=>	self::type,
				'default'			=>	self::default,
				'source_type'		=>	self::source_type,
				'source_default'	=>	self::source_default,
				'nullable'			=>	self::nullable,
				'description'		=>	self::description,
				'accessor'			=>	self::accessor,
				'reference_to'		=>	self::reference_to,
				'primary_accessor'	=>	self::primary_accessor,
				default				=>	nullstate::undeclared,
			};
		}
		elseif(is_int($which)){
			return match($which){
				self::label,				=>	'label',
				self::type,					=>	'type',
				self::default,				=>	'default',
				self::source_type,			=>	'source_type',
				self::source_default,		=>	'source_default',
				self::nullable,				=>	'nullable',
				self::description,			=>	'description',
				self::accessor,				=>	'accessor',
				self::reference_to,			=>	'reference_to',
				self::primary_accessor,		=>	'primary_accessor',
				default,					=>	nullstate::undeclared,
			};
		}
		return nullstate::undeclared;
	}

	public static function getProfile($field, $what=null) // yeah?
	{
		$info = null;
		switch ($what) {
			case self::label:
				$info = static::_approach_field_profile_[self::label][static::match($field)];
				break;
			case self::type:
				$info  = static::getType($field);
				break;
			case self::default:
				$info  = static::getDefault($field);
				break;
			case self::source_type:
				$info  = static::getSourceType($field);
				break;
			case self::source_default:
				$info  = static::getSourceDefault($field);
				break;
			case self::nullable:
				$info  = static::isNullable($field);
				break;
			case self::description:
				$info  = static::getDescription($field);
				break;
			case self::accessor:
				$info  = static::isAccessor($field);
				break;
			case self::reference_to:
				$info  = static::getReferenceByAccessor($field);
				break;
			case self::primary_accessor:
				$info  = static::getPrimaryAccessor();
				break;
			default:
				$info = [];
				foreach(static::getProfileProperties() as $property => $property_index){
					$info[$property] = static::getProfile($field, $property);
				}
				break;
		}
		return $info;
	}
}
