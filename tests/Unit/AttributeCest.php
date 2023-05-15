<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use \Approach\Render\Attribute;

class AttributeCest
{
    public function _before(UnitTester $I)
    {
    }

    // tests
    public function testKeyedAttribute(UnitTester $I)
    {
		$attribute = new Attribute('key', 'value');
		$I->assertEquals('key', $attribute->key);
		$I->assertEquals('value', $attribute->value);
		$I->assertEquals(' key="value"', $attribute->render());
    }

	public function testKeylessAttribute(UnitTester $I)
	{
		$attribute = new Attribute(null, 'value');
		$I->assertEquals(null, $attribute->key);
		$I->assertEquals('value', $attribute->value);
		$I->assertEquals(' value', $attribute->render());
	}

	public function testChainedKeylessAttribute(UnitTester $I)
	{
		$attribute = new \Approach\Render\Node(' ');

		// Add keyless nodes to the attribute
		$attribute[] = new Attribute(null,'classA');
		$attribute[] = new Attribute(null,'classB');
		$attribute[] = new Attribute(null,'classC');

		$I->assertEquals(' classA classB classC', $attribute->render());
	}
}
