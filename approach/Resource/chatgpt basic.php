<?php

	abstract class QB
	{
		const SELECT = 0;
		const FROM = 1;
		const WHERE = 2;
		const ORDER_BY = 3;
		const LIMIT = 4;
		const CALL = 5;
	}
	// method to translate the path-style string into a query
	function translate($path)
	{
		global $builder;
		// split the XPath string into its parts
		$parts = explode('/', $path);

		// iterate over the parts and add the corresponding QB clauses
		foreach ($parts as $part) {
			$choice = null;
			if ($part === 'select') {
				$builder->clause(QB::SELECT, ...getValues($part));
			} elseif ($part === 'from') {
				$builder->clause(QB::FROM, ...getValues($part));
			} elseif ($part === 'where') {
				$builder->clause(QB::WHERE, ...getValues($part));
			} elseif ($part === 'orderby') {
				$builder->clause(QB::ORDER_BY, ...getValues($part));
			} elseif ($part === 'limit') {
				$builder->clause(QB::LIMIT, ...getValues($part));
			} elseif ($part === 'call') {
				$builder->clause(QB::CALL, ...getValues($part));
			}
		}
	}

	function getValues($path, $part)
	{
		// get the part of the XPath string after the current part
		$remaining = strstr($path, $part);

		// get the values between square brackets
		preg_match_all('/\[([^\]]*)\]/',
			$remaining,
			$matches
		);
		return $matches[1];
	}
