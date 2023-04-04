<?php

namespace Approach\Service;

use \Approach\nullstate;

trait connectivity
{
	public static self $open_connections = [];

	public function connect(): nullstate
	{
		return nullstate::defined;
	}
	public function disconnect(): bool|null
	{
		return null;
	}
	public function send()
	{
	}
	public function recieve()
	{
	}
}
