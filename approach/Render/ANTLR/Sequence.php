<?php

namespace Approach\Render\ANTLR;

use \Approach\Render\Node;
use \Traversable;

class Sequence extends Node
{
	public $elements = [];

	public function __construct(array $options = [])
	{
		parent::__construct(...$options);
		$this->elements = $options['elements'] ?? [];
	}

	public function render()
	{
		$output = '';
		foreach ($this->elements as $element)
		{
			$output .= $element->render();
		}
		return $output;
	}
	
	public function RenderCorpus(): Traversable
	{
		foreach ($this->nodes as $n) {
			yield from $n->RenderHead();
			yield from $n->RenderCorpus();
			yield from $n->RenderTail();
		}

		yield null;
	}
}