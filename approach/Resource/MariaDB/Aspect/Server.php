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
		// Acceptable Qualities To Gather From $caller
		$symbols = [
			'HOST',
			'USER',
			'PASS',
			'DATABASE',
			'PORT',
			'SOCKET',
			'PERSISTENT'
		];
		$data = [];

		
		// foreach(\Approach\Resource\discoverability::$aspect_metadata['quality'] as $quality){
			
		// }
		foreach (get_class_vars($caller::class) as $key => $value){
			$ukey = strtoupper($key);
			if(in_array($ukey, $symbols )){
				// label // just the label?
				$data[$ukey]['label'] = $ukey;
				$data[$ukey]['description'] =null;
				$data[$ukey]['keywords'] =null;
				$data[$ukey]['children'] =null;
				$data[$ukey]['related'] =null;
				$data[$ukey]['type'] =null;
				$data[$ukey]['state'] = $caller->$key;
			}
		}

		$f = fopen('some_server.json', 'w');
		fwrite($f, json_encode($data));
		fclose($f);


		// Gather anything else you want into $data from $caller->connection (mysqli) or json files etc here

		return ['symbols' => $symbols, 'data' => $data, 'path' => $caller::get_aspect_directory()];
	}
}
