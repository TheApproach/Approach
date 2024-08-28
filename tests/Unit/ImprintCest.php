<?php

namespace Tests\Unit;

use Approach\Scope;
use Approach\path;
use Approach\runtime;

use \Approach\Imprint\Imprint;
use \Approach\Render\HTML;
use \Approach\Render\XML;
use \Approach\Render\Node;
use \Approach\nullstate;

// use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Html;
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
                path::installed->value => $path_to_approach,
            ],

            /*
             */
            mode: runtime::debug
        );

        /*
        # remove generated files by tests
        $file_path = $this->scope->getPath(path::imprint) . 'test/hellotoken/hello.php';
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        */
        
    }

    public function checkFromSupportDirectory(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/test.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $I->assertInstanceOf(Imprint::class, $imprint);
    }

    public function checkTemplateParsing(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/test.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');

        // echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER' . PHP_EOL; 
        // echo $imprint->pattern['display']->render();
        // echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER END' . PHP_EOL;
        // <html class=" " class="test" data-check="nacho [@ b @] mama">

        // <head class="">
        //     <title class="">Test File</title>
        // </head>

        // <body class="">
        //     <ul class=" " class="Screen">
        //         <li class=" " id="Header" class="Stage"></li>
        //         <li class=" " id="Main" class="Stage" color="blue" flavor="orange">
        //             <div class="">content [@ A @] here</div>
        //             <div class="">content [@ B @] here</div>
        //             <div class="">content [@ C @] here</div>
        //             <div class="">content [@ D @] here</div>
        //         </li>
        //         <li class=" " id="Footer" class="Stage [@ B @]">(C) 2022 Your Company Name Here</li>
        //     </ul>
        //     <ul class=" " class="OffScreen">
        //         <li cl ass=" " id="Props" class="Stage">.............</li>
        //     </ul>
        // </body>

        // </html>
    }

    public function checkPrepare(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');

        echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER' . PHP_EOL;
        // echo 'Classes: '.$imprint->pattern['hello'];
        // echo print_r($imprint->pattern['hello'], true);
        // echo $imprint->pattern['hello'];
        // // <div class=" " data-attrib="abc [@ attr_token @] abc">hi [@ person @]!</div>
        echo PHP_EOL . PHP_EOL . 'UNFORMATTED RENDER END' . PHP_EOL;
    }

    // public function checkTokenizing(UnitTester $I)
    // {
    // 	$imprint = new Imprint(
    // 		imprint: 'test/token_test.xml',
    // 		imprint_dir: $this->scope::$Active->GetPath(path::support)
    // 	);

    // 	$preparedSuccessful = $imprint->Prepare();

    // 	$I->assertTrue($preparedSuccessful, ' $node->Prepare() should return true ');
    // }


    /*
    
        Export Tree Roadmap

        convert a node tree in to an exported class file

        1. Recurse the tree
            - Imprint->exportTree()

        2. For each node, build the appropiate constructor
            - Imprint->exportConstructor()

        3. For each node, calculate the node's name 
            - based on parent and/or type and/or child index
            - Imprint->exportNodeName()

        4. For each node, gather dependencies for the constructo
            - classes
            - attributes
            - arguments is_a($valaue, Node:class)
            - Imprint->exportParameterBlocks()
        ...
        x. handle tokens in the tree
            - Imprint->exportTokenNodes()
        x. print lines to file
            - Imprint->exportFile()

        End: Output a class file
    */

    public function checkNodeName(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $hypertext0 = new HTML(tag: 'div', classes: ['test']);
        $hypertext1 = new HTML(tag: 'span', classes: ['sample']);
        $hypertext2 = new HTML(tag: 'div', classes: ['test', 'sample']);
        $markup0 = new XML( tag: 'item', attributes: ['sku' => '12345'] );
        $markup1 = new XML(tag: 'unit', attributes: ['sku' => '54321']);
        $markup2 = new XML(tag: 'item', attributes: ['sku' => '12345', 'color' => 'blue']);
        $node0 = new Node( 'test' );
        $node1 = new Node('sample');
        $node2 = new Node();

        $use_cases=[
            'Hypertext 0'   => [ $hypertext0 ],
            'Hypertext 1'   => [ $hypertext1 ],
            'Hypertext 2'   => [ $hypertext2 ],
            'Markup 0'      => [ $markup0 ],
            'Markup 1'      => [ $markup1 ],
            'Markup 2'      => [ $markup2 ],
            'Node 0'        => [ $node0 ],
            'Node 1'        => [ $node1 ],
            'Node 2'        => [ $node2 ],
            'Pattern root'  => [ $imprint->pattern['hello'] ],
        ];
        $samples = [
            'Hypertext 0'   => 'HTML_0',
            'Hypertext 1'   => 'HTML_1',
            'Hypertext 2'   => 'HTML_2',
            'Markup 0'      => 'XML_0',
            'Markup 1'      => 'XML_1',
            'Markup 2'      => 'XML_2',
            'Node 0'        => 'Node_0',
            'Node 1'        => 'Node_1',
            'Node 2'        => 'Node_2',
            'Pattern root'  => 'Node_3'
        ];
        foreach($use_cases as $key => $use_case){
            //... simulate instrucitons in exportTree() timeline leading up to where node name is instantiated
            $node_name = $imprint->exportNodeSymbol(...$use_case);
            $I->assertEquals(
                $samples[$key], 
                $node_name, 
                'Use case: '.$key.' returned ' . $samples[$key]
                // 'Arguments Passed: '.print_r($use_case, true)
            );
        }
    }

    public function checkExportConstructor(UnitTester $I){
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $use_cases=[];
        $samples = [];
        foreach($use_cases as $key => $use_case){
            //... simulate instrucitons in exportTree() timeline leading up to where node name is instantiated
            $constructor = $imprint->exportNodeConstructor(...$use_case);
            $I->assertEquals(
                $samples[$key], 
                $constructor, 
                ' $node->generateConstructor() should return ' . $samples[$key] . ' but returned ' . $constructor
            );
        }
    }

    public function checkExportParameterBlocks(UnitTester $I){
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $use_cases=[];
        $samples = [];
        foreach($use_cases as $key => $use_case){
            //... simulate instrucitons in exportTree() timeline leading up to where node name is instantiated
            $dependencies = $imprint->exportParameterBlocks(...$use_case);
            $I->assertEquals(
                $samples[$key], 
                $dependencies, 
                ' $node->generateParameterBlocks() should return ' . $samples[$key] . ' but returned ' . $dependencies
            );
        }
    }

    public function checkExportTokenNodes(UnitTester $I){
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $use_cases=[
        ];
        $samples = [];
        foreach($use_cases as $key => $use_case){
            //... simulate instrucitons in exportTree() timeline leading up to where node name is instantiated
            $token_nodes = $imprint->exportTokenNodes(...$use_case);
            $I->assertEquals(
                $samples[$key], 
                $token_nodes, 
                ' $node->generateTokenNodes() should return ' . $samples[$key] . ' but returned ' . $token_nodes
            );
        }
    }


    public function exportTreeMakesFile(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $imprint->Prepare();

        $imprint->Mint('hello'); // generate all  files
        $I->assertFileExists($this->scope->getPath(path::imprint) . 'test/hellotoken/hello.php');
    }

    public function exportTreeBuilds(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_dir: $this->scope->getPath(path::pattern)
        );

        $imprint->Prepare();

        $imprint->Mint('hello'); // generate all  files
        $I->assertFileExists($this->scope->getPath(path::imprint) . 'test/hellotoken/hello.php');
    }

    public function tryMintedClass(UnitTester $I)
    {
        /* Assumes the following:

            $imprint = new Imprint(
                imprint: 'test/hellotoken.xml',
                imprint_dir: $this->scope->getPath(path::pattern)
            );
            $exportedTrees = [];
            $imprint->Prepare();
            $imprint->Mint('hello');    // generate all  files
        */

        $hello = new \Approach\Imprint\test\hellotoken\hello();
        echo $hello;
    }

}

