<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

use \Approach\Render\Node as Node;

class NodeAccessCest
{


    // tests
    public function createNode(UnitTester $I)
    {
        $node = new Node('Hello World!');
        $I->assertInstanceOf(Node::class, $node);
    }

    public function createNodeContent(UnitTester $I)
    {
        $node = new Node('Hello World!');
        $I->assertEquals('Hello World!', $node->content);
    }

    public function createNestedNode(UnitTester $I)
    {
        $node_a = new Node('I am node A');
        $node_b = new Node('I am node B');

        $node_a['B'] = $node_b;

        $I->assertEquals('I am node B', $node_a['B']->content);
    }

    public function createDeepNestedNode(UnitTester $I)
    {
        $node_a = new Node('I am node A');
        $node_b = new Node('I am node B');
        $node_c = new Node('I am node C');

        $node_a['B'] = $node_b;
        $node_a['B']['C'] = $node_c;

        $I->assertEquals('I am node A', $node_a->content);
        $I->assertEquals('I am node B', $node_a['B']->content);
        $I->assertEquals('I am node C', $node_a['B']['C']->content);
    }
}
