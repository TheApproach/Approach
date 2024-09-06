<?php

namespace Approach\Resource\MariaDB\Aspect;

use \Approach\Resource\Aspect\discover;
use Approach\Scope;
use Approach\Resource\MariaDB\Aspect\profile;

/**
 * Server		- defines the types of aspects MariaDB\Server can have
 *				- defines the define_[aspect]() method for generating aspect const values
 * 
 * @package		Approach\Resource
 * @subpackage	MariaDB
 * @version		1.0.-1
 * @category	Resource
 * @category	Aspect
 * 
 */

class Server extends discover
{
	public static function define_qualities($caller)
	{
		// okay but still when I print out get_class_vars($caller::class) it doesn't show the constants
		// It shows a bunch of other stuff, but not the constants
		$symbols = [
			'HOST',
			'USER',
			'PASS',
			'DATABASE',
			'PORT',
			'SOCKET',
			'PERSISTENT',
			'CONNECTOR_CLASS',
		];

		$data = [];

		foreach (get_class_vars($caller::class) as $key => $value) {
			if (!isset($caller->{$key}) && defined($caller::class . '::' . $key)) {
				$caller->{$key} = constant($caller::class . '::' . $key);
			}
		}

		return ['symbols' => $symbols, 'data' => $data, 'path' => $caller::get_aspect_directory()];
	}
}
