<?php

namespace Approach\Resource;

use Stringable;
use Approach\Resource\Aspect\Aspect;

interface weighable
{
	public function weigh(string|Stringable|Aspect $aspect);
}
