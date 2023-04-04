<?php

namespace Approach\Render;

interface Stream
{
    public function RenderHead();
    public function RenderCorpus();
    public function RenderTail();
    public function render();
	public function toArray();
}
