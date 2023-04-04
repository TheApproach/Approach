<?php

namespace Approach\Render\JavaScript;

use Approach\Render;
use Traversable;

class Block extends Node
{
	public function __construct(
		public Render\Stream $before		= new Render\Node(),
		public Render\Stream $after			= new Render\Node(),
		public Render\Stream $prefix		= new Render\Node(content: '{'),
		public Render\Stream $suffix		= new Render\Node(content: '}')
	) {
	}

	public function RenderHead(): Traversable
	{
		yield PHP_EOL .
			Node::$depth . $this->before . PHP_EOL .
			Node::$depth . $this->prefix . PHP_EOL;
	}

    public function RenderCorpus(): Traversable
	{
		self::$depth = self::$depth . "\t";
		yield implode(self::$depth, $this->nodes);
	}

	public function RenderTail(): Traversable
	{
		// Chop one tab off
		Node::$depth =
			substr(Node::$depth, -1) === "\t"
			?	substr(Node::$depth, 0, -1)
			:	Node::$depth;
		yield PHP_EOL
			. Node::$depth . $this->suffix . PHP_EOL
			. Node::$depth . $this->after . PHP_EOL;
	}
	
}
