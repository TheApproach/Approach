<?php
namespace Approach\Imprint;
use \Approach\Render;
use \Approach\Render\Node\Keyed;

class temporary_alias_until_stream extends Keyed{}
class Token extends temporary_alias_until_stream{
	public function RenderCorpus()
	{
		$this->content = '';
		foreach($this->nodes as $node)
			$this->content .= $node->render();
	
		yield 	$this->content;
	}
	public function RenderHead(){
		yield '';
	}
	public function RenderTail(){
		yield '';
	}
}