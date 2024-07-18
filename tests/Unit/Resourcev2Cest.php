<?php


namespace Tests\Unit;

use Approach\Resource\Resource;
use Tests\Support\UnitTester;

class Resourcev2Cest
{
    public function findTest(UnitTester $I)
    {
        $url = "MariaDB://db.host/instances[rate gt 1000]/myDatabase/myTable[! price le 250 $ 5 AND id eq 1, status: active, updated: 12-31-2022][id, name].getFile()?hello=world";
        $resource = (new Resource($url));

        $resource->sift(['status']);
    }
}
