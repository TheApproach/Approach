<?php

namespace Approach\Render\ANTLR;

use \Approach\Render\Node;
use \Traversable;

class Option extends ANTLR
{
	protected string $key;
	protected string $value;

	public function __construct(string $key, string $value=null)
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function __toString()
	{
		return "{$this->key} = {$this->value}";
	}
}
