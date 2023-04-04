<?php

namespace Approach\Render;

use \Approach\Render;
use \Approach\Render\Attribute;
use \Approach\Render\Markup;
use \Stringable;
use Traversable;

class XML extends Render\Node implements Stream
{                                        // Uses All Markup Traits
    use Markup\Validation;
    use Markup\Properties;

    public function __construct(
        public null|string|Stringable $tag = NULL,
        public null|string|Stringable|Stream|self $content = null,
		public null|array|Attribute $attributes = new Attribute,
        public bool $prerender = false
    ) {
		if(is_array($attributes))
			$this->attributes = Attribute::fromArray($attributes);
		$this->set_render_id();
    }

    public function RenderHead(): Traversable
    {
        yield
            $this->before .
            '<' .
            $this->tag . $this->attributes .
            ($this->selfContained ?
                ' />' :
                '>' . $this->prefix
            )    //prefix and suffix don't really make sense on  self-contained elements
        ;            // :before <input value="abc" />  :after
    }

    public function RenderCorpus(): Traversable
    {
        if (!$this->prerender) {
            foreach ($this->nodes as $n) {
				yield from $n->RenderHead();
				yield from $n->RenderCorpus();
				yield from $n->RenderTail();

            }
            $this->prerender = true;
        }
		yield $this->content;
    }

    public function RenderTail(): Traversable
    {
        yield ($this->selfContained ?
            '' :
            $this->suffix . '</' . $this->tag . '>'
        ) .
            $this->after;
    }

    public static function GetByTag(&$root, string $tag)
    {
        $Store = [];

        foreach ($root->children as $child)   //Get Head
        {
            if ($child->tag == $tag) {
                $Store[] = $child;
            }

            foreach ($child->children as $children) {
                $Store = array_merge($Store, self::GetByTag($children, $tag));
            }
        }

        return $Store;
    }

    public static function GetFirstByTag(&$root, string $tag)
    {

        return self::GetByTag($root, $tag)[0] ?? null;
    }
}
