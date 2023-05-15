<?php

namespace Approach\Service;

use \Approach\nullstate;

trait connectivity
{
	public bool $connected = false;
	public mixed $connection;
	protected $alias=null;
	protected static array $connections = [];
	protected static $connection_limit = null;
	protected static $num_connected = 0;

	public function connect(): nullstate
	{
		// $state = nullstate::ambiguous;
		return nullstate::defined;
	}
	public function disconnect(): bool|null
	{
		return null;
	}
	public function Respond()
	{
	}
	public function Recieve()
	{
	}
}
