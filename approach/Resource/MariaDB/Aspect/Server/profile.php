<?php
namespace Approach\Resource\MariaDB\Aspect\Server;

use \Approach\Resource\Aspect\Aspect;
use \Approach\Resource\MariaDB\Aspect\quality as quality_meta;
use Approach\Resource\MariaDB\Aspect\Server\quality as SelfQuality;


class profile
{
static array $profile = [
	Aspect::operation => [
	],
	Aspect::field => [
	],
	Aspect::quality => [
		SelfQuality::HOST => [
			quality_meta::label => SelfQuality::label[ SelfQuality::HOST ],
			quality_meta::description => SelfQuality::description[ SelfQuality::HOST ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::HOST ],
			quality_meta::children => SelfQuality::children[ SelfQuality::HOST ],
			quality_meta::related => SelfQuality::related[ SelfQuality::HOST ],
			quality_meta::type => SelfQuality::type[ SelfQuality::HOST ],
			quality_meta::state => SelfQuality::state[ SelfQuality::HOST ],
		],
		SelfQuality::USER => [
			quality_meta::label => SelfQuality::label[ SelfQuality::USER ],
			quality_meta::description => SelfQuality::description[ SelfQuality::USER ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::USER ],
			quality_meta::children => SelfQuality::children[ SelfQuality::USER ],
			quality_meta::related => SelfQuality::related[ SelfQuality::USER ],
			quality_meta::type => SelfQuality::type[ SelfQuality::USER ],
			quality_meta::state => SelfQuality::state[ SelfQuality::USER ],
		],
		SelfQuality::PASS => [
			quality_meta::label => SelfQuality::label[ SelfQuality::PASS ],
			quality_meta::description => SelfQuality::description[ SelfQuality::PASS ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::PASS ],
			quality_meta::children => SelfQuality::children[ SelfQuality::PASS ],
			quality_meta::related => SelfQuality::related[ SelfQuality::PASS ],
			quality_meta::type => SelfQuality::type[ SelfQuality::PASS ],
			quality_meta::state => SelfQuality::state[ SelfQuality::PASS ],
		],
		SelfQuality::DATABASE => [
			quality_meta::label => SelfQuality::label[ SelfQuality::DATABASE ],
			quality_meta::description => SelfQuality::description[ SelfQuality::DATABASE ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::DATABASE ],
			quality_meta::children => SelfQuality::children[ SelfQuality::DATABASE ],
			quality_meta::related => SelfQuality::related[ SelfQuality::DATABASE ],
			quality_meta::type => SelfQuality::type[ SelfQuality::DATABASE ],
			quality_meta::state => SelfQuality::state[ SelfQuality::DATABASE ],
		],
		SelfQuality::PORT => [
			quality_meta::label => SelfQuality::label[ SelfQuality::PORT ],
			quality_meta::description => SelfQuality::description[ SelfQuality::PORT ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::PORT ],
			quality_meta::children => SelfQuality::children[ SelfQuality::PORT ],
			quality_meta::related => SelfQuality::related[ SelfQuality::PORT ],
			quality_meta::type => SelfQuality::type[ SelfQuality::PORT ],
			quality_meta::state => SelfQuality::state[ SelfQuality::PORT ],
		],
		SelfQuality::SOCKET => [
			quality_meta::label => SelfQuality::label[ SelfQuality::SOCKET ],
			quality_meta::description => SelfQuality::description[ SelfQuality::SOCKET ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::SOCKET ],
			quality_meta::children => SelfQuality::children[ SelfQuality::SOCKET ],
			quality_meta::related => SelfQuality::related[ SelfQuality::SOCKET ],
			quality_meta::type => SelfQuality::type[ SelfQuality::SOCKET ],
			quality_meta::state => SelfQuality::state[ SelfQuality::SOCKET ],
		],
		SelfQuality::PERSISTENT => [
			quality_meta::label => SelfQuality::label[ SelfQuality::PERSISTENT ],
			quality_meta::description => SelfQuality::description[ SelfQuality::PERSISTENT ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::PERSISTENT ],
			quality_meta::children => SelfQuality::children[ SelfQuality::PERSISTENT ],
			quality_meta::related => SelfQuality::related[ SelfQuality::PERSISTENT ],
			quality_meta::type => SelfQuality::type[ SelfQuality::PERSISTENT ],
			quality_meta::state => SelfQuality::state[ SelfQuality::PERSISTENT ],
		],
		SelfQuality::CONNECTOR_CLASS => [
			quality_meta::label => SelfQuality::label[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::description => SelfQuality::description[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::keywords => SelfQuality::keywords[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::children => SelfQuality::children[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::related => SelfQuality::related[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::type => SelfQuality::type[ SelfQuality::CONNECTOR_CLASS ],
			quality_meta::state => SelfQuality::state[ SelfQuality::CONNECTOR_CLASS ],
		],
	],
	Aspect::quantity => [
	],
	Aspect::map => [
	],
	Aspect::state => [
	],
	Aspect::access => [
	],
];
public static function GetProfile(){
	return [
		Aspect::quality => SelfQuality::GetProfile(),
	];
}
public static function GetCases(){
	return [
		Aspect::quality => SelfQuality::_case_map,
	];
}
public static function GetIndices(){
	return [
		Aspect::quality => SelfQuality::_index_map,
	];
}

}
