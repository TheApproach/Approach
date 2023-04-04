<?php

namespace Approach\Render\PHP;
use Approach\Render;
use Approach\Render\Node\Keyed;

class FunctionSignature extends Keyed
{

	public static $segmentation_phrase	= ' ';
	public static $associative_phrase 	= ' = ';
	public static $encapsulating_phrase	= '\'';
	public static $chaining_phrase		= ',';
    use Render\Associative;
}