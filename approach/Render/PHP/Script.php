<?php

namespace Approach\Render\PHP;

class Script extends Node
{
	public function RenderHead()
	{
		return '<?php' . PHP_EOL;
	}
	public function RenderTail()
	{
		return PHP_EOL;
	}
}
