<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

use Approach\Scope;
use Approach\path;


class ScopeCest
{
    // public function _before(UnitTester $I)
    // {
    // }

    // tests
    public function CreateScope(UnitTester $I)
    {
        $scope = new Scope();
        $I->assertInstanceOf(Scope::class, $scope);
    }

    public function CreateScopeWithRelativePaths(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../..';
        $path_to_approach = __DIR__ . '/../../approach/';
        $path_to_support = __DIR__ . '/../../support/';

        // echo PHP_EOL . PHP_EOL . 'PATH TO PROJECT: ' . $path_to_project . PHP_EOL . PHP_EOL;
        // echo PHP_EOL . PHP_EOL . 'PATH TO APPROACH: ' . $path_to_approach . PHP_EOL . PHP_EOL;

        $scope = new Scope(
            path: [
                path::project->value        =>  $path_to_project,
                path::installed->value      =>  $path_to_approach,
                path::support->value        =>  $path_to_support,
            ],
        );

        $I->assertEquals($path_to_project, $scope->GetPath(path::project));
        $I->assertEquals($path_to_approach, $scope->GetPath(path::installed));
        $I->assertEquals($path_to_support, $scope->GetPath(path::support));
    }
}
