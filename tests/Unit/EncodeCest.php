<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use \Approach\Service\Encode;
use \Approach\Service\format;

class EncodeCest
{
    public function encodeJson(UnitTester $I)
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $encoded = Encode::as(format::json, $data);

        $I->assertEquals(json_encode($data), $encoded);
    }

    public function registerExistingEncoderShouldThrowException(UnitTester $I)
    {
        $I->assertTrue(Encode::has(format::json));

        $I->expectThrowable(\Exception::class, function () {
            Encode::register(format::json, function ($data) {
                return json_encode($data);
            });
        });
    }

    public function registerNewEncoder(UnitTester $I)
    {
        $I->assertFalse(Encode::has(format::custom));

        Encode::register(format::custom, function ($data) {
            return true;
        });

        $I->assertTrue(Encode::has(format::custom));

        $I->assertTrue(Encode::as(format::custom, []));
    }
}
