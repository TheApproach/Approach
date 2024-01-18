<?php

namespace Tests\Unit;

use \Approach\deploy;
use \Tests\Support\UnitTester;
use \Approach\Service\CoolSpool\Connector;
use \Approach\Resource\CoolSpool\Spooler;
use \Approach\Resource;
use \Approach\Service;
use \Approach\Scope;
use \Approach\path;
use \Approach\nullstate;

/**
 * 	This is a Codeception Unit Test for the Approach\Service\CoolSpool class.
 * 
 * 	@package    Approach\Tests\Unit
 * 	@subpackage Approach\Tests\Unit\Service
 * 	@object     Approach\Tests\Unit\Service\CoolSpoolTest
 * 
 * 	@internal	
 *		[ ] This class may be tested for functionality in the future.
 * 		[ ] This class may be tested for security in the future.
 * 		[ ] This class may be tested for documentation in the future.
 * 		[ ] This class may be tested for performance in the future.
 * 
 * 	@dependencies
 * 		[ ] \Approach\Service\CoolSpool
 * 		[ ] \Approach\Resource\CoolSpool\Spooler
 * 		[ ] \Approach\Resource
 * 		[ ] \Approach\Service
 * 		[ ] \Approach\Scope
 * 		[ ] \Approach\path
 * 
 * 	@license    Apache 2.0
 * 	@version    0.0.1-alpha
 * 	@since      2023-02-04
 * 	@see        \Approach\Service\CoolSpool
 * 
 * 
 * 
 * 
 */


class CoolSpoolCest
{
	/**
	 * @var \Approach\Service\CoolSpool
	 * @var \Approach\Resource\CoolSpool\Spooler
	 * @var \Approach\Resource
	 * @var \Approach\Service
	 * @var \Approach\Scope
	 */

	protected Connector $connector;
	protected $Spooler;
	protected $scope;

	public function _before()
	{
		$path_to_project = __DIR__ . '/../../support/test_project';
		$path_to_approach = __DIR__ . '/../../approach';
		$path_to_support = __DIR__ . '/../../support';
		$path_to_vendor = __DIR__ . '/../../vendor';

		$root_project = 'suitespace.corp';
		$project_ensemble = 'myproject.' . $root_project;
		$project_data = 'data-00.' . $project_ensemble;

		$this->scope = new Scope(
			project: 'MyProject',
			path: [
				path::project->value        =>  $path_to_project,
				path::installed->value      =>  $path_to_approach,
				path::support->value        =>  $path_to_support,
				path::vendor->value			=>  $path_to_vendor,
			],
			deployment: [
				deploy::base->value         =>  $root_project,
				deploy::ensemble->value     =>  $project_ensemble,
				deploy::resource->value     =>  [
					'CoolSpool://SuiteUX'	=>	'avenuepad.com/search',
					'CoolSpool://Suitespace'=>	'service.suitespace.corp/',
					'MariaDB://SuiteUX'		=>	'data-00'.$project_ensemble,
				],
				deploy::resource_user->value =>  [
					'CoolSpool://SuiteUX'	=>	'tom',
					'CoolSpool://Suitespace'=>	'tom',
					'MariaDB://SuiteUX'		=>	'tom',
				],
			]
		);

		$this->Spooler = new Spooler(
			// host: 'typedcollection-01.system-00.suitespace.corp', //Scope::GetDeploy( deploy::resource ),
			// user: 'tom',//Scope::GetDeploy( deploy::resource_user ),
			// port: 3306,
			// pass: 'none',
			scope: 'https://demo8.suiteux.com/search',
			label: 'SuiteUX'
		);
	}

	public function _after()
	{

		if( isset($this->connector) ) {
			$this->connector->disconnect();
		}

		unset($this->connector);
		unset($this->Spooler);
	}

	// tests

	public function connectToTypedCollection(UnitTester $I)
	{
		$this->connector = new Connector();
		$state = $this->Spooler->connect();
		// $this->connector = $this->Spooler->connector;

		// Check if $state is a MySQLi error number or a nullstate enum instance.
		$I->assertTrue(
			$state instanceof nullstate ||
				is_int($state)
		);

		// If $state was a MySQLi error number, then output the error from the MySQLi connection at connector->connection
		if (!($state instanceof nullstate) && $state > 0) {
			$I->outputError($this->connector->connection->connect_error);
		} elseif ($state instanceof nullstate && $state !== nullstate::defined) {
			switch ($state) {
				case nullstate::undefined:
					$I->outputError('The connection state was undefined.');
					break;
				case nullstate::undeclared:
					$I->outputError('The connection state was undeclared.');
					break;
				case nullstate::ambiguous:
					$I->outputError('The connection state was ambiguous.');
					break;
				case nullstate::null:
					$I->outputError('The connection state was null.');
					break;
				default:
					$I->outputError('The connection state was vey ambiguous.');
					break;
			}
		}

		// If $state was nullstate::defined, then the connection was successful.
		$I->assertEquals($state, nullstate::defined);
	}



	public function checkSpoolerLevelDiscovery(UnitTester $I)
	{
		$this->Spooler->discover();
		// $Spooler = new \MyProject\Resource\MyData(pass: 'very $uper secret X10!');
		// $Spooler->discover();

		// Check if the Spooler has a php file at Scope::GetPath( path::project ) /Resource/
	}
}
