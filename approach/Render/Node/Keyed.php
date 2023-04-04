<?php

namespace Approach\Render\Node;

use \Approach\Render\Node;
use \Approach\Render\Associative;
use \Approach\Render\Stream;
use \Stringable;
use Traversable;

class Keyed extends Node implements \ArrayAccess
{
    use Associative;
    public static $segmentation_phrase    = ' ';
    public static $associative_phrase     = '=';
    public static $encapsulating_phrase   = '"';
    public static $chaining_phrase        = '';


    public function __construct(
        public null|string|Stringable|Stream|self $name =  null,
        public null|string|Stringable|Stream|self $content = null
    ) {
        $this->_keys[(string)$name] = $this;
    }

    public static function fromArray(array $dictionary)
    {
        $a = new static();
		if(\Approach\Approach::isArrayAssociative($dictionary))
			foreach ($dictionary as $k => $v) {
				$a[(string)$k] = $v;
			}
		else
			foreach ($dictionary as $v) {
				$a[] = $v;
			}
        return $a;
    }

    public function &toArray(): array
    {
        $a = [];
        foreach ($this->nodes as $node) {
            $a = array_merge($a, $node->toArray());
        }
        if ($this->name !== null)
            $a[(string)$this->name] = $this->content;

        return $a;
    }

    public function RenderHead(): Traversable
    {
		if($this->name === null && $this->content === null)
			yield '';
		else yield self::$segmentation_phrase;
    }

    /**
     * Generates a traversable corpus of the node and its descendants.
     *
     * @return Traversable
     */
    public function RenderCorpus(): Traversable
    {
        if ($this->name !== null) {
            yield (string)$this->name;
            if ($this->content !== null) {
                yield
                    self::$associative_phrase .
                    self::$encapsulating_phrase .
                    $this->content .
                    self::$encapsulating_phrase;
            }
        }
        yield '';
    }

    public function RenderTail(): Traversable
    {
        foreach ($this->nodes as $node) {
            yield
                $this->name !== null ?
                self::$chaining_phrase
                :    '';
            yield from $node->RenderHead();
            yield from $node->RenderCorpus();
            yield from $node->RenderTail();
        }
    }
}
