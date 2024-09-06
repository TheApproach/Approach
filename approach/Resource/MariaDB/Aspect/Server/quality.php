<?php
namespace Approach\Resource\MariaDB\Aspect\Server;

use \Approach\Resource\MariaDB\Aspect\quality as MariaDB_quality;

class quality extends MariaDB_quality

{

// Discovered Quality
	const _case_map = 0;
	const _index_map = 1;
	const HOST = 2;
	const USER = 3;
	const PASS = 4;
	const DATABASE = 5;
	const PORT = 6;
	const SOCKET = 7;
	const PERSISTENT = 8;
	const CONNECTOR_CLASS = 9;


// Discovered Quality Metadata
	const _approach_quality_profile_ = [
	];

}
