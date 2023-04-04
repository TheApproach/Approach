<?php

namespace Approach\Service;

use \Approach\Render\Node;
use \Approach\Resource\Resource;

trait sourceability
{

	public static self $acquired = [];
	abstract public static function aquire(Node $where): ?Node;
	abstract public static function pull(Node $where): ?Node;
	abstract public static function load(Node $where): ?Node;

	abstract public function save(Resource $where): ?bool;
	abstract public function push(Resource $where): ?bool;
	abstract public function release(Resource $where): ?bool;
}
