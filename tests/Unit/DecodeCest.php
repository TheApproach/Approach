<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use \Approach\Service\Decode;
use \Approach\Service\format;

class DecodeCest
{
    public function decodeJson(UnitTester $I)
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        $json = json_encode($data);

        $decoded = Decode::as(format::json, $json);

        $I->assertEquals(json_decode($json), $decoded);
    }

    public function registerExistingDecoderShouldThrowException(UnitTester $I)
    {
        $I->assertTrue(Decode::has(format::json));

        $I->expectThrowable(\Exception::class, function () {
            Decode::register(format::json, function ($data) {
                return json_decode($data);
            });
        });
    }

    public function registerNewDecoder(UnitTester $I)
    {
        $I->assertFalse(Decode::has(format::custom));

        Decode::register(format::custom, function ($data) {
            return true;
        });

        $I->assertTrue(Decode::has(format::custom));

        $I->assertTrue(Decode::as(format::custom, []));
    }
}
