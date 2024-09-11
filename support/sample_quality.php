<?php
namespace MyProject\Resource\MariaDB\Aspect\MyData;

use \Approach\Resource\MariaDB\Aspect\quality as MariaDB_quality;

class quality extends MariaDB_quality

{

// Discovered Quality
	const HOST = 0;
	const USER = 1;
	const PASS = 2;
	const DATABASE = 3;
	const PORT = 4;
	const SOCKET = 5;
	const PERSISTENT = 6;
	const CONNECTOR_CLASS = 7;


// Discovered Quality Metadata
	const _approach_quality_profile_ = [
		MariaDB_quality::label => [
			self::HOST =>	'HOST',
			self::USER =>	'USER',
			self::PASS =>	'PASS',
			self::DATABASE => 'DATABASE',
			self::PORT =>	'PORT',
			self::SOCKET =>	'SOCKET',
			self::PERSISTENT =>	'PERSISTENT',
			self::CONNECTOR_CLASS =>	'CONNECTOR_CLASS',
		],
		MariaDB_quality::description => [
			self::HOST => null,
			self::USER => null,
			self::PASS => null,
			self::DATABASE => null,
			self::PORT => null,
			self::SOCKET => null,
			self::PERSISTENT => null,
			self::CONNECTOR_CLASS => null,
		],
		MariaDB_quality::keywords => [
			self::HOST => null,
			self::USER => null,
			self::PASS => null,
			self::DATABASE => null,
			self::PORT => null,
			self::SOCKET => null,
			self::PERSISTENT => null,
			self::CONNECTOR_CLASS => null,
		],
		MariaDB_quality::children => [
			self::HOST => null,
			self::USER => null,
			self::PASS => null,
			self::DATABASE => null,
			self::PORT => null,
			self::SOCKET => null,
			self::PERSISTENT => null,
			self::CONNECTOR_CLASS => null,
		],
		MariaDB_quality::related => [
			self::HOST => null,
			self::USER => null,
			self::PASS => null,
			self::DATABASE => null,
			self::PORT => null,
			self::SOCKET => null,
			self::PERSISTENT => null,
			self::CONNECTOR_CLASS => null,
		],
		MariaDB_quality::type => [
			self::HOST => null,
			self::USER => null,
			self::PASS => null,
			self::DATABASE => null,
			self::PORT => null,
			self::SOCKET => null,
			self::PERSISTENT => null,
			self::CONNECTOR_CLASS => null,
		],
		MariaDB_quality::state => [
			self::HOST => 'localhost', 
			self::USER => 'root', 
			self::PASS => 'NoobScience', 
			self::DATABASE => 'test', 
			self::PORT => 3306, 
			self::SOCKET => '/var/run/mysqld/mysqld.sock', 
			self::PERSISTENT => true, 
			self::CONNECTOR_CLASS => null, 
		],
	];

}
