<?php


namespace Tests\Unit;

use Approach\Resource\Resource;
use Tests\Support\UnitTester;

class ParserCest
{
    public function checkResourceParse(UnitTester $I)
    {
        $url = "MariaDB://db.host/myDatabase/myTable[price: le 250, status: active, updated: 12-31-2022][id, name]";
        $r = Resource::parseUri($url);
        var_export($r);
        // $I->assertEquals($r['scheme'], "MariaDB");
    }
}