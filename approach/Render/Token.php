<?php

namespace Approach\Render;

class Token extends Node{
	public function __construct(
		public null|string|\Stringable|Stream $content = null,
		public null|string|\Stringable|Stream $name = null,
		public bool $prerender = false){}
}
