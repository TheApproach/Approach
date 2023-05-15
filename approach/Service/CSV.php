<?php
namespace Approach\Service;

use \Stringable;
use \Approach\nullstate;
use Approach\Resource\is;
use \Approach\Service\Decode;
use \Approach\Service\Encode;

class CSV
{
	public static $heading_size = 0;
	public static $autoheading = false;

	public static function decode(array $data)
	{
		$payload = array();
		foreach($data as $i => $content)
		{
			// Set CSV::heading_size to the number of lines to be used as headings
			if (CSV::$heading_size > 0) {
				$keys = str_getcsv($content);
				--CSV::$heading_size;
				while (CSV::$heading_size > 0) {
					$meta[] = str_getcsv($content);
					--CSV::$heading_size;
				}
			}

			// Otherwise, use the first line as headings if autoheading is true
			else if ($i == 0 && CSV::$autoheading)
				$keys = str_getcsv($content);
			
			// Otherwise, use the line as a record
			while ($scan = str_getcsv($content)) {
				$record = array();
				
				// If there are headings, use them as keys in the record
				if (isset($keys)) {
					foreach ($keys as $j => $key)
						$record[$key] = $scan[$j];
				}
				// Otherwise, the record is an indexed array of all items on the line
				else
					$record = $scan;

				$payload[] = $record;
			}
		}

		return $payload;
	}

	public static function encode(array $data)
	{
		foreach($data as $i => $target)
		{
			if(empty($target)) continue;
			$record = '';
			
			// If the array is associative, use the keys as headings
			if( self::is_associative($target) ){
				$keys = array_keys($target);
				$record.= implode(',', $keys).PHP_EOL;
			}

			// Encode the array as a CSV line
			foreach($target as $j => $item){
				$record .= implode(',', $item).PHP_EOL;
			}
			
			$payload[] = $record;
		}
		return $payload;
	}

	public static function is_associative(array $array): bool
	{
		foreach ($array as $key => $value) {
			if (!is_string($key)) return false;
		}
		return true;
	}

	public static function register(){
		// Pass CSV's static function decode to the Decode service as a callable
		Decode::register(format::csv, [CSV::class, 'decode']);
		Encode::register(format::csv, [CSV::class, 'encode']);
	}
}