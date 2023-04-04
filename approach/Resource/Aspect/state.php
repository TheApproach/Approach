<?php

namespace Approach\Resource\Aspect;

/**
 * state aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	State
 * @category	Status
 * @category	Condition
 * 
 */

class state extends Aspect
{
	public static $label;										// label for the state
	public static $description;									// description of the state
	public static $values;										// valid values for the state
	public static $initial;										// initial state of the resource
	public static $final;										// final state of the resource
	public static $transitions;									// transitions of the state
	public static $transitions_from;							// transitions from the state
	public static $transitions_to;								// transitions to the state
}
