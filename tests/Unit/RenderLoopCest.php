<?php


namespace Tests\Unit;

use Approach\Imprint\Imprint;
use Approach\path;
use Approach\runtime;
use Approach\Scope;
use Tests\Support\UnitTester;

class RenderLoopCest
{
    private Scope $scope;

    public function _before(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../../support/test_project';
        $path_to_approach = __DIR__ . '/../../approach/';
        $path_to_support = __DIR__ . '/../../support/';

        $this->scope = new Scope(
            project: 'MyProject',
            path: [
                path::project->value => $path_to_project,
                path::installed->value => $path_to_approach,
                path::support->value => $path_to_support,
                path::pattern->value => $path_to_support . 'pattern',
            ],
        );

    }

    // tests
    public function tryToTest(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'MariaDB/locate.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $imprint->Mint('locate');
    }
}
