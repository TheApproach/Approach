<?php

namespace Approach\Render\ANTLR;

use \Approach\Render\Node;
use \Traversable;
	

class Rule extends ANTLR
{
	protected $name;
	protected $sequence;

	public function __construct(string $name, Sequence $sequence, array $options = [])
	{
		$this->name = $name;
		$this->sequence = $sequence;
		parent::__construct(...$options);
	}
}