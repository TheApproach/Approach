<?php

namespace Approach\Imprint;
use Approach\Resource\selectable;

interface bondable{
	public function bind(...$target);
}
trait bondability{
	public function bind(selectable $scoped, Imprint $target){
		$symbols = $target->tokens;
		$values = $scoped->select();		
	}
}
class bound implements bondable
{
	use bondability;
}
