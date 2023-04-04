<?php

namespace Tests\Unit;

use \Approach;
use \Approach\nullstate;
use \Tests\Support\UnitTester;
use \Approach\Composition\Composition as Composition;

class CompositionCest
{
    public function _before(UnitTester $I)
    {
        $scope = new Approach\Scope();

        Composition::$types = [
            ''                  =>   1,
            'Dynamic'           => 777,
            'VendorDependency'  => 100
        ];

        Composition::$type_index = [
            1   => '',
            777 => 'Dynamic',
            100 => 'VendorDependency'
        ];

        Composition::$routes = [
            'example.com/'                  =>   1,
            'example.com/test'              => 777,
            'example.com/undefined'         => 100,
            'example.com/undefined_type'    =>  13
        ];
    }

    public function CreateComposition(UnitTester $I)
    {
        $comp = new Composition();
        $I->assertInstanceOf(Composition::class, $comp);
    }


    public function RouteFromCorrectUrl(UnitTester $I)
    {
        $composition = Composition::Route('example.com/test');

        $I->assertInstanceOf(Approach\Composition\Dynamic\handler::class, $composition);
    }

    public function RouteFromUndeclaredURL(UnitTester $I)
    {
        $composition = Composition::Route('example.com/undeclared');

        $I->assertEquals(nullstate::undeclared, $composition);
    }

    // TODO: tests will be re-enabled when the strict mode is implemented
    // public function RouteFromUndefinedUrl(UnitTester $I)
    // {
    //     // \Approach\nullstate::undeclared | string name instanceof Composition
    //     $composition = Composition::Route('example.com/undefined');

    //     $I->assertEquals(nullstate::undefined, $composition);
    // }

    public function RouteFromUndefinedTypeURL(UnitTester $I)
    {
        $composition = Composition::Route('example.com/undefined_type');

        $I->assertEquals(nullstate::undefined_type, $composition);
    }

    public function NestedRoute()
    {
    }
}
