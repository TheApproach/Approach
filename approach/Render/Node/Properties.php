<?php

namespace Approach\Render\Node;

use \Traversable;

trait Properties
{
    public static int $_render_count = 0;
    public int $_render_id = 0;
    public bool $prerender;

	public static function __set_state($properties){
		$node = new static;
		foreach($properties as $key => $value){
			$node->$key = $value;
		}
		return $node;
	}
    public function set_render_id()
    {
        $this->_render_id = static::$_render_count;
        $this->prerender = false;
        static::$_render_count++;
    }
    public function __toString()
    {
        return $this->render();
    }
    public function render()
    {
        $output = '';
        foreach ($this->RenderHead() as $r)
            $output .= $r;
        foreach ($this->RenderCorpus() as $r)
            $output .= $r;
        foreach ($this->RenderTail() as $r)
            $output .= $r;

        return $output;
    }

    public function RenderHead(): Traversable|\Approach\Render\Stream|string|\Stringable
    {
        //TODO: Implement RenderHead() method.
        yield '';
    }

    public function RenderCorpus(): Traversable|\Approach\Render\Stream|string|\Stringable
    {
		if(isset($this->content))
        yield $this->content;
        if (isset($this->prerender) && !$this->prerender) {
            foreach ($this->nodes as $n) {
                yield from $n->RenderHead();
                yield from $n->RenderCorpus();
                yield from $n->RenderTail();
                // $n->prerender = true;
            }
        }
    }

    public function RenderTail(): Traversable|\Approach\Render\Stream|string|\Stringable
    {
        //TODO: Implement RenderTail() method.
        yield null;
    }

    public function copyInto(&$into, $level = 255)
    {
        switch ($level) {
            case 255:
                $this->full_copyInto($into);
                break;
                //TO DO: Allow gradient copying of $level x $ChildNestDepthGapSize from 0-254,
                //When full_depth > 255, default to gap of full_depth % 256?
            case 1:
                $this->shallow_copyInto($into);
                break;
            default:
                $this->full_copyInto($into);
                break;
        }
        return $this;
    }

    public function full_copyInto(&$into)
    {
        $this->shallow_copyInto($into);
        for ($i = 0, $L = count($this->children); $i < $L; ++$i)    //Cascade
            $this->children[$i]->copyInto($into->children[$i]);
    }

    public function shallow_copyInto(&$into)
    {
        $into = clone $this;
        $this->set_render_id();
    }
}
