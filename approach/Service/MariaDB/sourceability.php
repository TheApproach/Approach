<?php

namespace Approach\Service\MariaDB;

use \Stringable;
use \Approach;
use \Approach\Resource\Resource;
use \Approach\Service\MariaDB;
use \Approach\nullstate;
use \MySQLi;

trait sourceability 
{

	public static $acquired = [];

	public static function aquire(string|Stringable|Resource $where, ...$options): ?Resource
	{
		$check_lock = $options['check_lock'] ?? null;		

		return new Resource('/');
	}

	public static function pull(string|Stringable|Resource $where, ...$options): ?Resource
	{
		return self::aquire($where, ...$options);
	}

	public static function load(string|Stringable |Resource $where, ...$options): ?Resource
	{
		$type = self::acquire($where)->find($where);
		$type::$cache[$where] =
			$type::$cache[$where]
			??
			self::pull($where, ...$options);

		return $type::$cache[$where];
	}

	public function save(string|Stringable|Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function push(string|Stringable|Resource $where): ?bool
	{
		if ($where) return true;
		return null;
	}

	public function release(string|Stringable|Resource $where): ?bool
	{
		/*
			Rollback active transactions

			Close and drop temporary tables

			Unlock tables

			Reset session variables

			Close prepared statements (always happens with PHP)

			Close handler
		*/
		if (!in_array($where, self::$acquired)) return true;
		return null;
	}

    public function discover(null|Resource $which): nullstate
    {
        foreach ($this->nodes as $server) {
            $server->discover();
        }
        $schemas = $this->inventory();
        $this->manifest_fields($schemas);
        return nullstate::defined;
    }
}


/**
 * 	Use a MariaDB service to handle data
 * 
 */