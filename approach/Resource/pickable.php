<?php

namespace Approach\Resource;

use Stringable;
use Approach\Resource\Aspect\Aspect;

interface pickable
{
	public function pick(string|Stringable|Aspect $aspect);
}
