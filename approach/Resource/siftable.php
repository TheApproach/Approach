<?php
namespace Approach\Resource;

use Stringable;
use Approach\Resource\Aspect\Aspect;

interface siftable{
	public function sift(string|Stringable|Aspect $aspect);
}