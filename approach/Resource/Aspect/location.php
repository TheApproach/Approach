<?php

namespace Approach\Resource\Aspect;


/**
 * location aspect class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.0
 * @category	Aspect
 * @category	Location
 * @category	URI
 * 
 */
class location extends Aspect
{
	public static $proto					= 'approach:/';		// protocol
	public static $prefix					= '/';				// path prefix such as /api/, /products/, /listings/FL_Miami/...
	public static bool $where				= '';				// where the resource is located, the remaining path components
	public static bool $relative			= true;				// location to be interpreted relative to the current context
	public static bool $is_recursive 		= false;			// location contains deeper levels of resources
	public static bool $requires_proto		= false;			// protocol is required
	public static bool $requires_prefix		= false;			// prefix is required
}
