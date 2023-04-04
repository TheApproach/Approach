<?php

namespace Approach;

use \Redis;

/**
 * Approach
 * 
 * @package		Approach
 * @subpackage	Shell
 * @version		2.0.0
 * @category	Approach
 * @category	Shell
 * @category	Utility Functions
 * 
 */


/**
 * recursive_glob() - recursively glob() a directory, returning an array of all files matching the criteria
 * 
 * @param string $criteria - the glob() criteria
 * @param int $flag - the glob() flag
 * @return array - an array of all files matching the criteria
 * 
 */

function recurive_glob($criteria, $flag = 0)
{
    $r = glob($criteria, $flag | GLOB_NOSORT);

    foreach(	
		glob(
			dirname($criteria).'/*',
			GLOB_NOSORT | GLOB_ONLYDIR
		)
		as $dir
	)
		$r = array_merge(
			[],
			...
			[
				$r, 
				recurive_glob(
					$dir . '/' . basename
						($criteria),
					$flag
				)
			]
		);
	
    return $r;
}

/**
 * recursive_copy() - recursively copy a directory
 * 
 * @param string $src - the source directory
 * @param string $dst - the destination directory
 * 
 */

function recursive_copy($src, $dst) 
{
	if (file_exists($dst)) {
		rmdir($dst);
	}
	if (is_dir($src)) {
		mkdir($dst);
		$files = scandir($src);
		foreach ($files as $file)
		if ($file != "." && $file != "..")
			recursive_copy("$src/$file", "$dst/$file");
	}
	else if (file_exists($src)) {
		copy($src, $dst);
	}
}

/**
 * resolveUriSelector() - parse a URL sub-component for a selector
 * 
 * @param string $selector_string - the URL sub-component
 * @return array - an array of the selector's type and range
 * 
 */

function resolveUriSelector($selector_string)
{
	// Parse selector portion of URL sub-component
	$cursor = 0;

	$index	= [
		'open_type'		=>	false,			// Component Name
		'close_type'	=>	false,
		// 'open_group'	=>	false,			// Component Group
		// 'close_group'	=>	false,
		'open_range'	=>	false,			// Component Group Index
		'close_range'	=>	false
	];
	$selector_type = NULL;	// If no range at all
	$selector_range = '*';	// If you ignored the 2nd part of range, select all. Same as [Type][*]
	$remainder = NULL;

	// Scan URL sub-component for square brackets in mysite.com/page[Type][0..99]
	$L = strlen($selector_string);
	for (; $cursor < $L; $cursor++)
	{
		$character = $selector_string[$cursor];

		if (false === $index['open_type'] && $character === '[')
		{
			$index['open_type'] = $cursor;
		}
		else if (false === $index['close_type'] && $character === ']')
		{
			$index['close_type'] = $cursor;
		}
		// else if (false === $index['open_group'] && $character === '['){
		// 	$index['open_group'] = $cursor;
		// } else if (false === $index['close_group'] && $character === ']'){
		// 	$index['close_group'] = $cursor;
		// }
		else if (false === $index['open_range'] && $character === '[')
		{
			$index['open_range'] = $cursor;
		}
		else if (false === $index['close_range'] && $character === ']')
		{
			$index['close_range'] = $cursor;
		}
	}

	// Extract component type name From URL mysite.com/page[Type][0..99]
	if (false !== $index['open_type'] && false !== $index['close_type'])
	{
		$selector_type = substr($selector_string, $index['open_type'] + 1, $index['close_type'] - $index['open_type'] - 1);
		if ($L > $index['close_type'])
		{
			$remainder = substr($selector_string, $index['close_type'] + 1);
		}
		else
		{
			$remainder = NULL;
		}
	}

	// if(false !== $index['open_group'] && false !== $index['close_group']){
	// 	$selector_type = substr($selector_string, $index['open_group']+1, $index['close_group'] - $index['open_group']-1);
	// 	if( $L > $index['close_group']){
	// 		$remainder = substr($selector_string, $index['close_group']+1);
	// 	} else{
	// 		$remainder = NULL;
	// 	}
	// }

	// Extract indexed range From URL mysite.com/page[Type][0..99]
	if (false !== $index['open_range'] && false !== $index['close_range'])
	{
		$selector_range = substr($selector_string, $index['open_range'] + 1, $index['close_range'] - $index['open_range'] - 1);
		if ($L > $index['close_range'])
		{
			$remainder = substr($selector_string, $index['close_range'] + 1);
		}
		else
		{
			$remainder = NULL;
		}

		$range_text = trim($selector_range);
		if (strpos($range_text, '..') !== false)
		{							// Selecting a range of components
			$selector_range = explode('..', $range_text);
			$selector_range[0] = trim($selector_range[0]);
			$selector_range[1] = trim($selector_range[1]);
		}
		elseif ($range_text == '*')
		{										// Selecting all components
			$selector_range = [NULL, NULL];
		}
		elseif (is_numeric($range_text))
		{								// Selecting single components
			$selector_range = [(int)$range_text];
		}
		else
		{															// Maybe someone customized ResolveComponents and ComponentList uses keys
			$selector = $range_text;
		}
	}

	return [
		'selector_type'		=> $selector_type,
		// 'selector_group'	=> $selector_type,
		'selector_range' 	=> $selector_range,
		'remainder'			=> $remainder
	];
}

/**
 * setCookie() - set a cookie
 * 
 * @param string $name - the name of the cookie
 * @param string $value - the value of the cookie
 * @param int $expire - the expiration time of the cookie
 * @param string $path - the path of the cookie
 * @param string $domain - the domain of the cookie
 * @param bool $secure - whether the cookie is secure
 * @param bool $httponly - whether the cookie is http only
 * 
 */

function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
{
	if (headers_sent())
	{
		return false;
	}

	if (PHP_VERSION_ID < 70300)
	{
		return setcookie($name, $value, $expire, $path . '; samesite=lax', $domain, $secure, $httponly);
	}

	return setcookie($name, $value, [
		'expires' => $expire,
		'path' => $path,
		'domain' => $domain,
		'secure' => $secure,
		'httponly' => $httponly,
		'samesite' => 'Lax'
	]);
}


/**
 * setSession() - Make the PHP session handler use Redis
 * 
 * @param string $host - the host of the redis server
 * @param int $port - the port of the redis server
 * @param string $password - the password of the redis server
 * @param int $timeout - the timeout of the redis server
 * @param string $prefix - the prefix of the redis server
 * 
 * @return bool - whether the session handler was set
 * 
 */

function setSession($host, $port = 6379, $password = '', $timeout = 0, $prefix = 'PHPREDIS_SESSION:')
{
	if (!extension_loaded('redis'))
	{
		trigger_error('Redis extension not loaded', E_USER_WARNING);
		return false;
	}

	$redis = new Redis();
	$redis->connect($host, $port, $timeout);

	if ($password !== '')
	{
		$redis->auth($password);
	}

	$redis->setOption(Redis::OPT_PREFIX, $prefix);
	$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

	session_set_save_handler(
		[$redis, 'open'],
		[$redis, 'close'],
		[$redis, 'read'],
		[$redis, 'write'],
		[$redis, 'destroy'],
		[$redis, 'gc']
	);

	return true;
}