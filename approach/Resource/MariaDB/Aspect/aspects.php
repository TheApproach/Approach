<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Resource\Aspect\aspects;

/**
 * aspects enum - defines the types of aspects Resource classes can have
 *				- defines the manifest() method for generating Aspect classes
 * 
 * @package		Approach
 * @subpackage	Resource
 * @version		2.0.-1
 * @category	Aspect
 * @category	Location
 * @category	URI
 * 
 */

class aspect extends aspects
{
	case table;
	case view;
	case procedure;
}