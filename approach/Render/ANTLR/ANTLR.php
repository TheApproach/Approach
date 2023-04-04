<?php

namespace Approach\Render\ANTLR;

use \Approach\Render\Node;
use \Traversable;


/*
This class represents the top-level structure of an ANTLR grammar file. It has three properties: $header, $rules, and $footer, which correspond to the header section, the list of rules, and the footer section of the ANTLR grammar file, respectively.

The RenderHead(), RenderCorpus(), and RenderTail() methods allow us to generate the corresponding sections of the ANTLR grammar file by concatenating the Render() output of each of the Rule objects in the $rules property, and returning the $header and $footer properties as strings.

This should give us a good foundation for generating an ANTLR grammar file using the Approach rendering system.
*/

class ANTLR extends Node
{
	public $gammarName;
	public $header;
	public $rules;
	public $footer;

	public function addRule( Rule $rule)
	{
		$this->rules[] = $rule;
	}


	public function __construct($gammarName = 'myGrammar', ...$options)
	{
		parent::__construct(...$options);
	}

	public function RenderHead(): string
	{
		return $this->header;
	}

	public function RenderCorpus(): string
	{
		$corpus = '';
		foreach ($this->rules as $rule)
		{
			$corpus .= $rule->Render();
		}
		return $corpus;
	}

	public function RenderTail(): string
	{
		return $this->footer;
	}
}
