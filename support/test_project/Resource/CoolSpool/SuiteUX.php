<?php

namespace MyProject\Resource\CoolSpool;

class SuiteUX extends \Approach\Resource\CoolSpool\Spooler
{
	use SuiteUX_user_trait;
	const SCOPE = 'https://demo8.suiteux.com/search';
	const HOST = 'demo8.suiteux.com';
	const PERSISTENT = '1';
	const IS_POOLED = '1';
	const CONNECTOR_CLASS = '\Approach\Service\CoolSpool\Connector';
	
	
}
