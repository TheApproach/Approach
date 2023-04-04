<?php


namespace Tests\Unit;

use \Approach\Service\Service;
use Tests\Support\UnitTester;

class ServiceCest
{
	public function _before(UnitTester $I)
	{
		// Instantiate the Branch class here
	}

	public function createServiceWithDefaults(UnitTester $I)
	{
		$service = new Service();

        $I->assertInstanceOf(Service::class, $service);
	}

	
}

