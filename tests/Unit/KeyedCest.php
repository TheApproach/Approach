<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

use Approach\Render\Node;
use Approach\Render\Node\Keyed;

class KeyedCest
{
    public function _before(UnitTester $I)
    {
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
        $fake_index = 5;
        $pretend_token = new Node(content: 'key');
        $pretend_token2 = new Node(content: 'myvalue:1');

        $key = new Node();
            $key[] = new Node(content: 'data-');
            $key[] = $pretend_token;
            $key[] = new Node(content: (string) $fake_index);

        // <tag id="taco" data-[@ token @]="myclass_[@ token @]_5" >...</tag>;

        $value = new Node();
            $value[] = new Node(content: '{');
            $value[] = new Node(content: $pretend_token2);
            $value[] = new Node(content: '}');

        $node = new Keyed($key, $value);

        // $a = $node->toArray();
        // var_export($a);
        // $x = $node->get('data-key5');

        $I->assertEquals(' data-key5="{myvalue:1}"', $node['data-key5']->render());

        $pretend_token2->content = 'myvalue:2';
        $I->assertEquals(' data-key5="{myvalue:2}"',  $node['data-key5']->render());

        $node['data-key5'] = new Node('newvalue');
        $I->assertEquals(' data-key5="newvalue"', $node['data-key5']->render());

        $pretend_token->content = 'attr';
        $node['data-attr5'] = new Node('newvalue');
        $I->assertEquals(' data-attr5="newvalue"', $node['data-attr5']->render());
    }
}
