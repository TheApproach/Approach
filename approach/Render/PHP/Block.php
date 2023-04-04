<?php

namespace Approach\Render\PHP;

use Approach\Render;

class Block extends Node
{
	public function __construct(
		public Render\Stream $before		= new Render\Node(),
		public Render\Stream $after			= new Render\Node(),
		public Render\Stream $prefix		= new Render\Node(content: '{'),
		public Render\Stream $suffix		= new Render\Node(content: '}'),
	) {
	}

	public function RenderHead()
	{
		return PHP_EOL .
			Node::$depth . $this->before . PHP_EOL .
			Node::$depth . $this->prefix . PHP_EOL;
	}
	public function RenderTail()
	{
		// Chop one tab off
		Node::$depth =
			substr(Node::$depth, -1) === "\t"
			?	substr(Node::$depth, 0, -1)
			:	Node::$depth;
		return PHP_EOL
			. Node::$depth . $this->suffix . PHP_EOL
			. Node::$depth . $this->after . PHP_EOL;
	}
	public function RenderCorpus()
	{
		self::$depth = self::$depth . "\t";
		return implode(self::$depth, $this->nodes);
	}
}
