<?php

namespace Approach\Render\PHP;


class ClassBlock extends Block
{
	use modifiers;
	public function RenderHead()
	{
		return PHP_EOL . 'class ' . $this->name . ' ' . $this->modifiers . ' {' . PHP_EOL;
	}
}
