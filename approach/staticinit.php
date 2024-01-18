<?php

namespace Approach;

#[\Attribute(\Attribute::TARGET_CLASS)]
class init{

	public function __construct( string $class ){
		$class::__static_init();
	}
}