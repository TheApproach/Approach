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

enum aspects
{
	case container;
	case location;
	case operation;
	case field;
	case quality;
	case quantity;
	case map;
	case state;
	case authorization;

	public static function manifest(\Stringable|string|Resource $resource)
	{
		// Derrive the namespace from the given resource's underlying class
		$namespace = get_class($resource);
		$namespace = substr($namespace, 0, strrpos($namespace, '\\'));

		// Scan the namespace for Aspect classes matching this enum's cases
		$cases = self::cases();
		$aspects = [];
		foreach ($cases as $case)
		{
			$aspect = $namespace . '\\' . $case;
			if ( ($aspect instanceof Aspect) && ($resource instanceof Resource))
			{
				$aspects[$case] = $resource::define($aspect);		// Build a tree of Aspect objects
			}
			else
			{
				$aspect::manifest();						// Generate Aspect classes
			}
		}
		return $aspects;
	}
}
