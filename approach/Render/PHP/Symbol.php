<?php

namespace Approach\Render\PHP;

class Symbol extends Node
{
	public function __construct(
		public $prefix = '$'
	) {
	}

	public function RenderHead()
	{
		return '$' . $this->content;
	}
}
