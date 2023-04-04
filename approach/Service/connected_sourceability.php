<?php

namespace Approach\Service;

use \Stringable;
use \Approach\nullstate;
use \Approach\Render\Node;
use \Approach\Render\Stream;
use \Approach\Resource\Resource;


trait connected_sourceability
{
	use connectivity;
	use sourceability;

	public static function aquire(Stringable|string $where): ?Resource
	{
		return new Resource('/');
	}

	public static function pull(Stringable|string $where): ?Resource
	{
		return self::aquire($where);
	}

	public static function load(Stringable|string  $where, ...$opt): ?Resource
	{
		$type = self::acquire($where)->find($where);
		return $type::$cache[$where] =
			$type::$cache[$where]
					??
			self::pull($where);
	}

	public function save(Resource $what, null|string|Stringable|Stream|Node|Resource $where = null): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function push(Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function release(Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}





	public function transport()
	{
		// Flow::IN
		// promise
		// if !connected, connect
		// if !acquired source, acquire source
		// if !loaded source resource(s), load source resource(s)

		// or
		// Flow::OUT
		// promise
		// if !connected, connect
		// if !acquired target, acquire target
		// push pre-acquired resource to target
	}

	public function promise()
	{
		// use amp? guzzle? fibers?
	}

	public function act($where, $intent, ...$support)
	{
		// promise 
		// if !connected, connect
		// if !acquired $where, acquire $where
		// promise
		// send $intent & $support to acquired $where
		// recieve response resource 
	}

	// two-way transport 
	// from Source|Resource pair to Source|Resource pair
	public function exchange()
	{
		// if !connected, connect
		// if !acquired target, acquire target

		// acquire 

		// transport() source resource with Flow::IN
		// transport() to target resource with Flow::OUT

		// promise
		// push source resources to target location & release if possible
		// recieve response resource
		// if !released, release target
		// return response resource

		// see approach/Dataset/exchangeTransport->prep_exchange()
	}
	public function bestow($from, $to, $who)
	{
		// authentication check
		// permissions check
		// transfer ownership
	}
	public function locate($where)
	{
		// robust fetch
	}
	public function interact()
	{
		// multi-step calls to act, recieve, handler
	}

	public function discover(null|Resource $which): nullstate
	{
		return nullstate::defined;
	}
}
