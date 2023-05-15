<?php

namespace Approach\Resource;

use Stringable;
use Approach\Resource\Aspect\Aspect;

interface locatable
{
	public function locate(string|Stringable|Aspect $aspect);
}
