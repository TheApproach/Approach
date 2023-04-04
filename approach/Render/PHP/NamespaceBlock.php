<?php

namespace Approach\Render\PHP;


class NamespaceBlock extends Block
{
	use modifiers;
	public function RenderHead()
	{
		return PHP_EOL . 'namespace ' . $this->name . ' ' . $this->modifiers . ' {' . PHP_EOL;
	}
}
