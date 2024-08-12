<?php

namespace Approach\Render;

use \Approach\Render\Node;
use \Approach\Render\Stream;
use \Stringable;
use \Closure;

class Conditional extends Node
{
	public function __construct(
		public $content = null,
		public $condition = null
	) {
		if (
			!($condition instanceof Closure)	&&
			!is_callable($condition)			&&
			!is_bool($condition)				&&
			!($condition instanceof Token)
		) {
			if (static::exempt($condition)) {
				$this->condition = static::convert($condition);
			} else throw new \Exception('ifNode condition must be a Closure, callable, or boolean.');
		}

		// $this->condition is a bool or something we can call to get a bool
		$this->condition = $condition;
		parent::__construct($content);
	}

	public static function exempt($exempt)
	{
		// Allow things we can static::convert() to bool
		return is_string($exempt) || is_int($exempt);
	}

	public static function convert($condition)
	{
		if (is_bool($condition)) {
			return $condition;
		}
		if (is_string($condition)) {
			$condition = strtolower(trim($condition));
			if ($condition === 'true' || $condition === '1') {
				return true;
			} else if ($condition === 'false' || $condition === '0') {
				return false;
			} else throw new \Exception('Render\ifNode could not convert() string to boolean faithfully.');
		}
		if (is_int($condition)) {
			$error_level = error_reporting();
			if ($condition > 1 && $error_level & E_USER_WARNING) {
				trigger_error(
					'Render\ifNode hit an ambiguous cast from int ' . $condition . ' to bool.' . PHP_EOL .
						'Called from: ' . debug_backtrace()[0]['file'] . ':' . debug_backtrace()[0]['line'],
					E_USER_WARNING
				);
			}
			return $condition === 1;
		} elseif (is_callable($condition) || $condition instanceof Closure) {
			return static::convert(static::$condition());
		}
		return false;
	}
	
	public function RenderCorpus(): \Traversable|Stream|string|Stringable
	{
		// echo PHP_EOL . 'ifNode::RenderCorpus()';
		$condition = $this->condition;
		// echo PHP_EOL . 'with condition: ' . var_dump($condition);

		if ($this->condition instanceof Token) {
			$condition = $this->condition->content;
		}
		if (is_callable($condition) || $condition instanceof Closure) {
			$condition =
				static::convert(
					($condition)()
				);
		}

		if ($condition === true )
		{
			yield $this->content;

			foreach ($this->nodes as $n) {
				yield from $n->stream();
			}
		}
	}
}
