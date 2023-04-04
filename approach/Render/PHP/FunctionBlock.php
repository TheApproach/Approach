<?php

namespace Approach\Render\PHP;

use Approach\Render;

class FunctionBlock extends Block
{
	use modifiers;
	public function __construct(
		public FunctionSignature $argument_list = new FunctionSignature
	) {
	}
	public function RenderHead()
	{
		return PHP_EOL .
			$this->modifiers . 
			' function ' . $this->name . '( ' .
				$this->arguments .
			' )' . PHP_EOL . '{';
	}
}
