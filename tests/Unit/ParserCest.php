<?php

namespace Tests\Unit;

use Approach\Resource\Resource;
use Approach\path;
use Approach\Scope;
use Tests\Support\UnitTester;
use Approach\Resource\MariaDB\Table;
class ParserCest
{
    public $scope;
    public function _before(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../../support/test_project/';
        $path_to_approach = __DIR__ . '/../../approach/';
        $path_to_support = __DIR__ . '/../../support/';

        $this->scope = new Scope(
            path: [
                path::project->value        =>  $path_to_project,
                path::installed->value      =>  $path_to_approach,
                path::support->value        =>  $path_to_support,
            ],
        );
    }
    public function checkResourcePackage(UnitTester $I): void
    {
        echo PHP_EOL;
        $x=Table::get_package_name();
        var_export($x);
    }
    public function checkResourceParse(UnitTester $I): void
    {
        $url = 'MariaDB://db.host/instances[rate gt 1000]/myDatabase/myTable[! price le 250 $ 5 AND id eq 1, status: active, updated: 12-31-2022][id, name].getFile()?hello=world';
        $r = Resource::parseUri($url);
        // var_export($r);
        $I->assertEquals($r['result']['scheme'], 'MariaDB');
    }
}
