<?php

namespace Tests\Unit;

// require __DIR__ . '/../../vendor/autoload.php';
// require __DIR__ . '/../../approach/Render/Node.php';
// require __DIR__ . '/../../approach/Render/KeyedNode.php';


use Tests\Support\UnitTester;
use \Approach\Render;
use \Approach\Render\ANTLR\ANTLR;
use \Approach\Render\ANTLR\Sequence;
use \Approach\Render\ANTLR\Token;
use \Approach\Render\ANTLR\Option;
use \Approach\Render\ANTLR\Rule;

class RenderCest
{
    // public function _before(UnitTester $I)
    // {  }

    // tests
    public function createNode(UnitTester $I)
    {
        $node = new Render\Node();
        $I->assertInstanceOf(Render\Node::class, $node);
    }

    public function testNodeChildRendering(UnitTester $I)
    {
        $node = new Render\Node(content: 'Hello ');
        $node->nodes[] = new Render\Node(content: 'World!');

        $I->assertEquals('Hello World!', $node->render());
    }

    public function createKeyedNode(UnitTester $I)
    {
        $node = new Render\Node\Keyed();
        $I->assertInstanceOf(Render\Node\Keyed::class, $node);
    }

    public function createHTML(UnitTester $I)
    {
        $html = new Render\HTML(tag: 'html');
        // $html->SkipRenderCascade = true;
        $I->assertInstanceOf(Render\HTML::class, $html);

        $head = new Render\HTML(tag: 'head');
        $body = new Render\HTML(tag: 'body', classes: ['renderable_2']);
        $div = new Render\HTML(tag: 'div', classes: ['renderable_3'], content: 'Hello World!');

        $html->nodes[] = $head;
        $html->nodes[] = $body;
        $body->nodes[] = $div;

        // TODO: Use SimpleXML to validate the output is valid HTML
        // with a <head> and <body> tag and Hello World! in the <div>
        $sample = '<html><head></head><body class="renderable_2"><div class="renderable_3">Hello World!</div></body></html>';

        $output = $html->render();

        // echo $node->render();

        $I->assertEquals($sample, $output);
    }


    public function createSelfContainedHTML(UnitTester $I)
    {
        $link = new Render\HTML(tag: 'link', selfContained: true);

        $link->attributes->rel = 'stylesheet';
        $link->attributes['href'] = 'style.css';

        $sample = '<link rel="stylesheet" href="style.css" />';

        $output = $link->render();

        $I->assertEquals($sample, $output);
    }

    public function createJavaScriptDocumentReady(UnitTester $I)
    {

        $DocumentReady = new Render\JavaScript\DocumentReady();
        $DocumentReady->nodes[] = new Render\Node(content: 'const x = 1;');

        $sample = '
$(document).ready(function() {
const x = 1;
});
';

        $output = $DocumentReady->render();

        $I->assertEquals($sample, $output);
    }
/*
	function testANTLR(UnitTester $I)
	{
		// Create the calculator ANTLR
		$calculator = new ANTLR('Calculator');

		// Define the tokens
		$number = new Token('number', '[0-9]+(\.[0-9]+)?');
		$plus = new Token('plus', '\+');
		$minus = new Token('minus', '-');
		$times = new Token('times', '\*');
		$divide = new Token('divide', '/');
		$lparen = new Token('lparen', '\(');
		$rparen = new Token('rparen', '\)');

		// Define the options
		$expr = new Option('expr');
		$term = new Option('term');
		$factor = new Option('factor');

		// Define the rules
		$calculator->addRule(new Rule($expr,   new Sequence([$term, $expr])));
		$calculator->addRule(new Rule($expr,   new Sequence([$term])));
		$calculator->addRule(new Rule($term,   new Sequence([$factor, $times, $term])));
		$calculator->addRule(new Rule($term,   new Sequence([$factor, $divide, $term])));
		$calculator->addRule(new Rule($term,   new Sequence([$factor])));
		$calculator->addRule(new Rule($factor, new Sequence([$number])));
		$calculator->addRule(new Rule($factor, new Sequence([$lparen, $expr, $rparen])));

		// Define the sequence
		$sequence = new Sequence([$expr, new Token('eof', '\$')]);

		// Set the sequence
		$calculator->addRule( new Rule('eofff', $sequence));

		// Render the ANTLR to a .g4 file
		echo $calculator->render();
	}
	*/		
}

// $lexer = new \Approach\Render\ANTLR\RenderLexer();
// $tokens = new \Antlr\Runtime\CommonTokenStream($lexer);
// $parser = new \Approach\Render\ANTLR\RenderParser($tokens);
// $parser->setTreeAdaptor(new \Approach\Render\ANTLR\RenderTreeAdaptor());
// $parser->setTokenStream($tokens);
// $parser->setTrace(true);
// $parser->setDebug(true);
// $parser->document();
// $tree = $parser->getTree();
// $walker = new \Approach\Render\ANTLR\RenderTreeWalker();
// $walker->walk(new \Approach\Render\ANTLR\RenderTreeListener(), $tree);
// // $I->assertEquals($sample, $output);