<?php

namespace Approach\Service;

use \Approach\Render\Node;
use \Approach\Resource\Resource;
use \Approach\nullstate;

interface sourceable
{
	public static function aquire(...$opt):Resource;
	public static function pull(...$opt):Resource;
	public static function load(...$opt):Resource;

	public function save(...$opt):nullstate;
	public function push(...$opt):nullstate;
	public function release(...$opt):nullstate;

	public function discover(...$opt):Resource;
}
