<?php

namespace Approach\Render\PHP;

class Statement extends Node
{
	use modifiers;		// eg "public static"
	private bool $assignment = false;

	public function __construct(
		public mixed $content,
		public Symbol $assignee = null
	) {
		if ($this->assignee !== null) {
			$this->assignment = true;
		}
	}

	public function RenderHead()
	{
		if ($this->assignment) {
			return Node::$depth . $this->modifiers . $this->assignee . ' = ';
		} else return $this->modifiers . '';
	}

	public function RenderTail()
	{
		return ';' . PHP_EOL;
	}
}
