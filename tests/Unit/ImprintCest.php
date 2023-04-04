<?php

namespace Tests\Unit;

use Approach\Scope;
use Approach\path;
use Approach\runtime;

use \Approach\Imprint\Imprint;
use \Approach\nullstate;
use Tests\Support\UnitTester;

class ImprintCest
{
    private Scope $scope;
    
    public function _before(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../../approach';
        $path_to_approach = __DIR__ . '/../../approach';

        $this->scope = new Scope(
            path: [
                path::project->value => $path_to_project,
                path::installed->value    =>     $path_to_approach,
            ],

            /*
			*/
            mode: runtime::debug
        );
    }

    // public function checkTemplateParsing(UnitTester $I)
    // {
    // 	$node = new Imprint(
    // 		imprint: 'test/test.xml'
    // 	);

    // 	$preparedSuccessful = $node->Prepare();

    // 	$I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');
    // }

    public function checkFromSupportDirectory(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/test.xml',
            imprint_base: $this->scope->getPath(path::pattern)
        );

        $I->assertInstanceOf(Imprint::class, $imprint);
    }

    public function checkTemplateParsing(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/test.xml',
            imprint_base: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');

        // echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER' . PHP_EOL;
        // echo $imprint->pattern['display']->render();
        // echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER END' . PHP_EOL;

        // =" <Pattern class=""   name="display"></Pattern> "
    }

    // public function checkTokenizing(UnitTester $I)
    // {
    // 	$imprint = new Imprint(
    // 		imprint: 'test/token_test.xml',
    // 		imprint_base: $this->scope::$Active->GetPath(path::support)
    // 	);

    // 	$preparedSuccessful = $imprint->Prepare();

    // 	$I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');
    // }

    public function exportTree(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/test.xml',
            imprint_base: $this->scope->getPath(path::pattern)
        );
        $exportedTrees = [];
        $imprint->Prepare();

        //$imprint->pattern[$which]; 	// tree to export is here
        // foreach ($imprint->pattern as $which => $tree) {
        // }
        // $exportedTrees['display'] = $imprint->exportTree($imprint->pattern['display'], '\\Approach\\Render\\Node');

        echo PHP_EOL . PHP_EOL . 'FILE ' . PHP_EOL . PHP_EOL;
        // echo $imprint->print('display');
        // $I->assertEquals(nullstate::defined, $imprint->mint('display'));    // generate string of a single Imprint pattern

        $imprint->Mint('display');    // generate all  files


        // foreach($exportedTrees['display'] as $line)
        // 	echo $line.PHP_EOL;
        echo PHP_EOL .  'FILE END' . PHP_EOL;
    }
}

/* UNFORMATTED RENDER
<Pattern class=""   name="display" type="HTML"><html class=""  ><head class=""  ><title class=""  ></title></head><body class=""  ><ul class="Screen"   class="Screen"><li class="Stage"   id="Header" class="Stage"></li><li class="Stage"   id="Main" class="Stage"><div class=""  ></div></li><li class="Stage [@ B @]"   id="Footer" class="Stage [@ B @]"></li></ul><ul class="OffScreen"   class="OffScreen"><li class="Stage"   id="Props" class="Stage"></li></ul></body></html></Pattern>
*/

/* FORMATTED RENDER
<Pattern class="" name="display" type="HTML">
    <html class="">

    <head class="">
        <title class=""></title>
    </head>

    <body class="">
        <ul class="Screen" class="Screen">
            <li class="Stage" id="Header" class="Stage"></li>
            <li class="Stage" id="Main" class="Stage">
                <div class=""></div>
            </li>
            <li class="Stage [@ B @]" id="Footer" class="Stage [@ B @]"></li>
        </ul>
        <ul class="OffScreen" class="OffScreen">
            <li class="Stage" id="Props" class="Stage"></li>
        </ul>
    </body>

    </html>
</Pattern>
*/