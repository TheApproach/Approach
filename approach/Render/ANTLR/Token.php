<?php

namespace Approach\Render\ANTLR;

use \Approach\Render\Node;
use \Traversable;

class Token extends ANTLR
{
	protected string $token;

	public function __construct(string $token)
	{
		$this->token = $token;
	}

	public function __toString()
	{
		return $this->token;
	}
}