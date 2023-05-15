<?php

namespace Approach\Resource\MariaDB;

use \Approach\Resource\Resource;

/**
 * MariaDB Table resource class
 * 
 * @package		Approach
 * @subpackage	Resource
 * @subpackage	MariaDB
 * @version		2.0.0
 * @category	Resource
 * @see			https://approach.orchestrationsyndicate.com/docs/2.0/resource/mariadb/table
 * 
 */

// Allow table to use dynamic property names
// Since this was deprecated without an attribute we will add the attribute here

#[\AllowDynamicProperties]
 class Table extends Resource{

 }