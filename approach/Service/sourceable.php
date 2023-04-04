<?php

namespace Approach\Service;

use \Approach\Render\Node;
use \Approach\Resource\Resource;
use \Approach\nullstate;

interface sourceable
{
	public static function aquire(Node $where): ?Node;
	public static function pull(Node $where): ?Node;
	public static function load(Node $where): ?Node;

	public function save(Resource $where): ?bool;
	public function push(Resource $where): ?bool;
	public function release(Resource $where): ?bool;

	public function discover(null|Resource $which): nullstate;
}
