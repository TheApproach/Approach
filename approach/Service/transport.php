<?php

namespace Approach\Service;

/*
	When indexing an array of labels, use the following constants.
	When indexing an array of in/out functions, use the following constants % 2,
	so that the in/out functions are paired. In will always be 0, out will always be 1.

	$myobj->action[transport::format][self::in] = $format;
	$myobj->action[transport::target][self::in] = $target;
	$myobj->action[transport::format][self::out] = $format;
	$myobj->action[transport::target][self::out] = $target;

*/

abstract class transport{
	const format = 0;
	const target = 1;

	const load = 2;
	const save = 3;
	
	const extract = 4;
	const inject = 5;
	
	const transform = 6;
	const filter = 7;

	const encode 	= 4;
	const decode 	= 5;
	
	const parse 	= 6;
	const generate 	= 7;

}