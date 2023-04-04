<?php

namespace Approach\Render\JavaScript;

use \Traversable;

class DocumentReady extends Block
{

	public function RenderHead(): Traversable
	{
		yield PHP_EOL . '$(document).ready(function() {' . PHP_EOL;
	}

    public function RenderTail(): Traversable
    {
        yield PHP_EOL . '});' . PHP_EOL;
    }
}
