<?php

namespace Approach\Render\PHP;


class TraitBlock extends Block
{
	use modifiers;
	public function RenderHead()
	{
		return PHP_EOL . 'trait ' . $this->name . ' ' . $this->modifiers . ' {' . PHP_EOL;
	}
}
