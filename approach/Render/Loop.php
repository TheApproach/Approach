<?php

namespace Approach\Render;

use \Approach\Render\Node;
use \Approach\Render\Stream;
use \Stringable;
use \Closure;

class Loop extends Node
{
	public function __construct(
		public $content = null,
		public $what = [],
		public $as = ''
	) {
		if (
			!($what instanceof Closure)	&&
			!is_callable($what)			&&
			!is_array($what)			&&
			!($what instanceof Node)
		) {
			if (static::exempt($what)) {
				$this->what = static::convert($what);
			} 
			else throw 
				new \Exception(
					'Render\\Loop->what must be some kind of loopable container.'
				);
		}

		// $this->what is a iterable or something we can call to get a iterable
		$this->what = $what;
		parent::__construct($content);
	}

	public static function exempt($exempt)
	{
		// Allow things we can static::convert() to iterable
		// Nothing besides what is in the constructor so far
		return false;
	}

	public static function convert($what)
	{
		// Yea idk what else one would loop. We'll probably put Iterable in the constructor so it doesn't go here.
		// Maybe identifiers or deserializeable strings could go here. 
		// Just a convention to allow child classes use of specialization

		return $what;
	}

	public function RenderCorpus(): \Traversable|Stream|string|Stringable
	{
		// echo PHP_EOL . 'ifNode::RenderCorpus()';
		$what = $this->what;
		// echo PHP_EOL . 'with what: ' . var_dump($what);

		if ($this->what instanceof Token) {
			$what = $this->what->content;
		}

		if (is_callable($what) || $what instanceof Closure) {
			$what =
				static::convert(
					($what)()
				);
		}

		// Check if $what is something foreach-able
		if( !is_iterable($what) ){
			throw new \Exception(
				'Render\Loop\'s "what" must be some kind of loopable container... hence the name.'
			);
		}

		foreach($what as $k => $v){
			$match_bracket 	= $this->as . '[' . $k . ']';
			$match_dot 		= $this->as . '.' . $k;

			foreach ($this->nodes as $n) {

				/**
				 * @codestyle Conditional/Complex
				 * Complex if statement code style:
				 * 
				 * Leaving opening and closing ( ) on their own lines
				 * when nesting makes it easier to read.
				 * 
				 * We keep operators aligned vertically and indentation
				 * 	- Nesting operators
				 *  - Comparison operators
				 *  - Logical operators
				 * 
				 * Benefits:
				 * - allows groups of conditions to be easily identified
				 * - allows for condition chaining without mangling punctuation
				 * - allows for easy addition of new conditions
				 * - simple to explain and understand
				 * - universal, works in any language
				 * 
				 */
				if( $n instanceof Token			&&
				  (
					$n->name === $this->as		|| 
				  	$n->name === $match_bracket	|| 
					$n->name === $match_dot
				  )
				  ) $n->content = $v;

				yield from $n->stream();
			}
		}
	}
}
