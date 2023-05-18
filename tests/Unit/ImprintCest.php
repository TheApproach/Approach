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
            imprint_base: $this->scope->getPath(path::pattern)
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
    // 		imprint_base: $this->scope::$Active->GetPath(path::support)
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
            imprint_base: $this->scope->getPath(path::pattern)
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
            imprint_base: $this->scope->getPath(path::pattern)
        );

        $preparedSuccessful = $imprint->Prepare();

        $use_cases=[];
        $samples = [];
        foreach($use_cases as $key => $use_case){
            //... simulate instrucitons in exportTree() timeline leading up to where node name is instantiated
            $constructor = $imprint->exportConstructor(...$use_case);
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
            imprint_base: $this->scope->getPath(path::pattern)
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
            imprint_base: $this->scope->getPath(path::pattern)
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
            imprint_base: $this->scope->getPath(path::pattern)
        );

        $imprint->Prepare();

        $imprint->Mint('hello'); // generate all  files
        $I->assertFileExists($this->scope->getPath(path::imprint) . 'test/hellotoken/hello.php');
    }

    public function exportTreeBuilds(UnitTester $I)
    {
        $imprint = new Imprint(
            imprint: 'test/hellotoken.xml',
            imprint_base: $this->scope->getPath(path::pattern)
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
                imprint_base: $this->scope->getPath(path::pattern)
            );
            $exportedTrees = [];
            $imprint->Prepare();
            $imprint->Mint('hello');    // generate all  files
        */

        $hello = new \Approach\Imprint\test\hellotoken\hello();
        echo $hello;
    }

}


/*
I have this render system. Some functionality is left out for brevity

class Node{
    public array $nodes = [];
    public $content = '';

    // ...
    //All nodes are stringable, so they can be used as content in other nodes.
    // They implement __toString() and their own render pipelines
}

class Attribute extends Node{
    public $name = '';
    public $content = '';

    //... implements ArrayObject access over $this->nodes
    // For classes, name is blank
    // child nodes are flattened into content for both classes and attributes. keys are ignored when flattening.
    // This is so Nodes can be used in attributes and classes as name or content
    // Tokens in turn can be stored as a subnode of a class or attribute, when using a Node.
    
}

class Token extends Node{
    public $name ='';
}

class XML extends Node{
    public $id;
    public $attributes = [];
}

class HTML extends XML{
    public $tag = 'div';
    public Attribute|array $classes = [];
    public Attribute|array $attributes = [];
}

*/




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




/**
 *  ParameterBlocks below
 *
 */

/// FILE: ./approach/Render/Streamability.php
// <?php

// namespace Approach\Render;
// use \Approach\nullstate;
// use \Stringable;

// trait Streamability 
// {
// 	private array $_labeled_nodes = [];		
// 	private array $_node_labels = [];		// string keys 

// 	public function &toArray()
// 	{
// 		return [
// 			$this->getNodeProperties(),
// 			...$this->nodes->getNodeProperties() 
// 		];
// 	}

// 	public function __set(mixed $label, mixed $val)
// 	{
// 		$this->offsetSet($label, $val);
// 	}

// 	public function __get(mixed $label)
// 	{
// 		return $this->offsetGet($label);
// 	}

// 	public function getNodeProperties()
// 	{
// 		return [
// 			...$this->getHeadProperties(),
// 			...$this->getCorpusProperties(),
// 			...$this->getTailProperties()
// 		];
// 	}

// 	protected function getHeadProperties(): array
// 	{
// 		return [];
// 	}
// 	protected function getCorpusProperties(): array
// 	{
// 		return [
// 			'content'	=> $this->content		/**<	TODO: make enum labels	>*/
// 		];
// 	}
// 	protected function getTailProperties(): array
// 	{
// 		return [];
// 	}

// 	public function offsetExists($label): bool|nullstate
// 	{
// 		if (is_int($label))	return isset($this->nodes[$label]);
// 		else return
// 			isset($this->_labeled_nodes[$label])
// 			?(
// 				isset($this->nodes[$this->_labeled_nodes[$label]])
// 				?
// 					true
// 				:	false //nullstate::undefined
// 			)
// 			:	false; //nullstate::undeclared;
// 	}

// 	public function offsetGet(mixed $label): mixed
// 	{
// 		if (is_int($label))	return

// 			// If the label is actually a direct offset to the nodes array, return it
// 			$this->getLabeledNode($label)
// 			??
// 			// If a provided index is not in the array, return nullstate::undeclared
// 			nullstate::undeclared;

// 		$label_index = $this->getNodeLabelIndex($label);
// 		// echo 'label ';
// 		// var_dump($label);
// 		// echo PHP_EOL . 'index ';
// 		// var_dump($label_index);

// 		// echo PHP_EOL . 'labeled nodes ';
// 		// var_dump($this->_labeled_nodes);

// 		// echo PHP_EOL . 'node labels ';
// 		// var_dump($this->_node_labels);

// 		return 
// 			// If the label exists
// 			$label_index !== nullstate::undeclared
// 			?
// 				// If the label points to an existing node                
// 				$this->getLabeledNode( $label_index )

// 			:	// Or else, the label was never declared	
// 				nullstate::undefined;
// 	}

// 	public function offsetSet(mixed $label, mixed $value): void
// 	{
// 		if ($label === null){
// 			$this->nodes[] = $value;
// 			return;
// 		}

// 		$label_index = $this->getNodeLabelIndex($label);

// 		if($label_index !== nullstate::undeclared && $this->getLabeledNode($label_index) !== nullstate::undefined)
// 		{
// 			// echo 'getting label index... found: '.$label_index.PHP_EOL;
// 			$selected = $this->getLabeledNode( $label_index );
// 			// echo 'getting labeled node... found: ' . get_class($selected) . PHP_EOL;

// 			$selected = &$value;
// 			// echo 'setting labeled node to a ' . get_class($value) . PHP_EOL;
// 		}
// 		else
// 		{
// 			// echo 'getting label index... undeclared. adding... '  . PHP_EOL;

// 			$this->nodes[] = $value;								// Actual Nodes, Not all labels
// 			$node_index = count($this->_node_labels);				// Index of the soon to be added label
// 			$this->_node_labels[] = $label;							// Push the label to the label array
// 			$this->_labeled_nodes[$node_index] = end($this->nodes);	// Label Index Storage
// 		}
// 	}

// 	protected function getLabeledNode(int $label_index)
// 	{
// 		return
// 			$this->_labeled_nodes[$label_index]
// 			??
// 			nullstate::undefined;
// 	}

// 	/**
// 	 * Returns an index that works with $this->_labeled_node[...] to find a node you labeled
// 	 * 
// 	 * @param string $label
// 	 * @return int|null
// 	 */
// 	protected function getNodeLabelIndex(string|Stringable $label)
// 	{
// 		$offset = array_search($label, $this->_node_labels);
// 		// echo PHP_EOL.'looking for label index.. found: '.$offset.PHP_EOL;
// 		return $offset !== false ?
// 			$offset
// 		:	nullstate::undeclared
// 		;
// 	}

// 	public function offsetUnset(mixed $label): void
// 	{
// 		if (is_int($label) && isset( $this->nodes[$label] )){
// 			unset($this->nodes[$label]);
// 			return;
// 		}

// 		if(isset($this?->nodes[$this?->_labeled_nodes[$label]]))
// 			unset($this->nodes[$this->_labeled_nodes[$label]]);

// 		return;
// 	}
// }

// /// FILE: ./approach/Render/Node/Properties.php

// <?php	// Path: approach\Render\Node\Properties.php

// namespace Approach\Render\Node;

// use Approach\nullstate;
// use Approach\Render\Node;

// /**
//  * 	@package Approach
//  * 	@subpackage Render
//  * 	@version 2.0.-1 beta
//  * 
//  * 	@license Apache 2.0
//  * 	@since	2023-02-04
//  * 	@see	\Approach\Render\Node
//  * 
//  */

// trait Properties
// {
//     public static int $_render_count = 0;			// Can this become optional?
//     public int $_render_id = 0;						// Can this become optional?
//     public bool $prerender;							// Can this become optional?

// 	/**
// 	 * The code defines several rendering methods and a copy method for a PHP class.
// 	 * 
// 	 * @param properties An array of properties to set on the object being created in the __set_state
// 	 * method.
// 	 * 
// 	 * @return The code snippet contains several methods that perform different actions and return
// 	 * different values.
// 	 */
// 	public static function __set_state($properties){
// 		$node = new static(...$properties);
// 		foreach($properties as $key => $value){
// 			$node->$key = $value;
// 		}
// 		return $node;
// 	}
//     /**
// 	 * This function sets a render ID and increments the render count.
// 	 */
// 	public function set_render_id()
//     {
//         $this->_render_id = static::$_render_count;
//         $this->prerender = false;
//         static::$_render_count++;
//     }

// 	/**
// 	 * This recursively searches for a node with a specific ID within a tree structure.
// 	 * 
// 	 * @param root This is a reference to the root node of a tree structure.
// 	 * @param _render_id _render_id is a parameter that represents the unique identifier of a node in a
// 	 * tree structure. The function is designed to search for a node with a specific _render_id and return
// 	 * it if found.
// 	 * 
// 	 * @return Node|nullstate either a Node object or nullstate::null (which is likely a null value).
// 	 */
// 	public static function GetById(&$root, $_render_id): Node|nullstate
// 	{
// 		if ($root->_render_id == $_render_id) return $root;

// 		foreach ($root->children as $child)
// 		{
// 			$result = self::GetById($child, $_render_id);

// 			if ($result instanceof self)
// 			{
// 				if ($result->_render_id == $_render_id) return $result;
// 			}
// 		}

// 		return nullstate::null;
// 	}

// 	/**
// 	 * The function copies data from one variable to another with the option to specify the level of depth.
// 	 * 
// 	 * @param into A reference to the object that the current object will be copied into.
// 	 * @param level The level parameter is an optional integer value that determines the depth of the copy.
// 	 * It has a default value of 255, which means a full copy will be made. A value of 1 means a shallow
// 	 * copy will be made.
// 	 * @return the current object instance ().
// 	 */
//     public function copyInto(&$into, $level = 255)
//     {
//         switch ($level) {
//             case 255:
//                 $this->full_copyInto($into);
//                 break;
//                 //TO DO: Allow gradient copying of $level x $ChildNestDepthGapSize from 0-254,
//                 //When full_depth > 255, default to gap of full_depth % 256?
//             case 1:
//                 $this->shallow_copyInto($into);
//                 break;
//             default:
//                 $this->full_copyInto($into);
//                 break;
//         }
//         return $this;
//     }

//     /**
// 	 * This function performs a full copy of an object and its children into another object.
// 	 * 
// 	 * @param into  is a reference to the object that the current object () will be copied
// 	 * into. The function full_copyInto() is used to create a full copy of the current object and all
// 	 * its child objects, and copy them into the  object.
// 	 */
// 	public function full_copyInto(&$into)
//     {
//         $this->shallow_copyInto($into);
//         for ($i = 0, $L = count($this->children); $i < $L; ++$i)    //Cascade
//             $this->children[$i]->copyInto($into->children[$i]);
//     }

//     /**
// 	 * This function creates a shallow copy of an object and sets a render ID.
// 	 * 
// 	 * @param into  is a reference to the variable that will receive the shallow copy of the
// 	 * object. The "&" symbol before the parameter name indicates that it is a reference parameter,
// 	 * meaning that any changes made to the parameter inside the function will also affect the original
// 	 * variable outside the function.
// 	 */
// 	public function shallow_copyInto(&$into)
//     {
//         $into = clone $this;
//         $this->set_render_id();
//     }
// }

// /// File: ./approach/Render/Container.php

// <?php

// /**
//  * 
//  * This is the Container class, which is the base class for all Approach\Render objects.
//  * 
//  * Takes advantage of Greenspun's Tenth Rule:
//   		"Any sufficiently complicated C or Fortran program contains an ad hoc, 
//   			informally-specified, bug-ridden, slow implementation of half of Common Lisp."
//  															- Philip Greenspun
//  * 
//  * Greenspun refer's to the fact that Common Lisp is a language that is capable of being 
//  * used to implement any other language, including itself.  This is because Common Lisp is
//  * a homoiconic language, which means that it can treat its own code as data, and its data
//  * as code.
//  * 
//  * Importantly, LISPs are very capable when it comes to stream processing.  
//  * A stream represents a sequence of elements, which are processed in series. 
//  * Approach's Render pipeline is heavily influenced by the stream processing paradigm.
//  * Every Node object is a stream, and every Nodes may be made to stream themselves, as an
//  * entire tree, subtree, node instance or rendered output.
//  * 
//  * Since all Containers and Node objects have a List and a Keyed interface, they are
//  * capable of being processed as streams.  This means that containers and nodes can
//  * be composed by Approach\Composition\Composition, which is a stream
//  * processor. All for simply implementing RenderHead(), RenderCorpus(), and RenderTail() 
//  * for your given format or specialized element.
//  * 
//  * Streams are very powerful because they are not limited to being processed by one function.
//  * Streams can be processed by many functions, in a chain, or a pipeline.  This is called
//  * "stream composition", and is a very powerful way to process data.
//  * 
//  * Streams can also be processed by functions that take one or multiple streams as input, and return
//  * one or multiple streams as output.  This is called "stream multiplexing and demultiplexing", a 
//  * powerful way to accelerate generic data processing.
//  * 
//  * Given that all complex systems end up implementing a subset of Lisp, our subset of Lisp
//  * is the most powerful subset of Lisp that we could create. You can further customize the storage 
//  * mechanism of your Node or Container, by altering the ArrayObject implementation and electing 
//  * some form of ordering for render() and stream().
//  * 
//  * With all other Layer built on top of Render, you can create any data structure, and render it
//  * in any format, and stream it to any destination. Physical and logical separation of concerns
//  * is one of the most important aspects of software engineering.  Approach takes this to heart,
//  * and provides a solid foundation for building any kind of application through rich typing
//  * and semantic definition.
//  * 
//  * @package Approach
//  * @subpackage Render
//  * @version 2.0.-1 beta
//  * 
//  * @license Apache 2.0
//  * @since	2023-02-04
//  * @see	https://orchetrationsyndicate.com/Approach/Render/Container
//  * 
//  */

// namespace Approach\Render;

// use \Approach\nullstate as nullstate;
// use \Approach\Render\Stream;
// use \Traversable;
// use \Stringable;

// class Container extends \ArrayObject implements Stream
// {
// 	private array $_labeled_nodes = [];	
// 	private array $_node_labels = [];	

// 	public $nodes = [];
// 	use Streamability;

// 	/**
// 	 * This creates a new Node object with properties set from an array.
// 	 * 
// 	 * @param array properties An array of properties that need to be set for the Node object.
// 	 * 
// 	 * @return Node A Node object.
// 	 */
// 	public static function __set_state(array $properties): Node
// 	{
// 		$node = new Node();
// 		if(isset($properties['content']))
// 			$node->content = $properties['content'];
// 		if(isset($properties['prerender']))
// 			$node->prerender = $properties['prerender'];
// 		if(isset($properties['nodes']))
// 			$node->nodes = $properties['nodes'];		
// 		if(isset($properties['_render_id']))
// 			$node->_render_id = $properties['_render_id'];
// 		return $node;
// 	}

// 	public function __toString()
// 	{
// 		return $this->render();
// 	}
// 	/**
// 	 * The function renders content of a Node tree.
// 	 * 
// 	 * @return The `render()` method returns a string that is the concatenation of the results of
// 	 * calling the `RenderHead()`, `RenderCorpus()`, and `RenderTail()` methods.
// 	 */
// 	public function render() : \Stringable|string|Stream
// 	{
// 		$output = '';
// 		foreach ($this->RenderHead() as $r)
// 			$output .= $r;
// 		foreach ($this->RenderCorpus() as $r)
// 			$output .= $r;
// 		foreach ($this->RenderTail() as $r)
// 			$output .= $r;

// 		return $output;
// 	}

// 	public function RenderHead(): Traversable|\Approach\Render\Stream|string|\Stringable
// 	{
// 		//TODO: Implement RenderHead() method.
// 		yield '';
// 	}


// 	/**
// 	 * This function renders the content and nodes of a Node tree.
// 	 */
// 	public function RenderCorpus(): Traversable|\Approach\Render\Stream|string|\Stringable
// 	{
// 		if (isset($this->content))
// 			yield $this->content;
// 		if (isset($this->prerender) && !$this->prerender)
// 		{
// 			foreach ($this->nodes as $n)
// 			{
// 				yield from $n->RenderHead();
// 				yield from $n->RenderCorpus();
// 				yield from $n->RenderTail();
// 				// $n->prerender = true;
// 			}
// 		}
// 	}

// 	/**
// 	 * This function renders the tail of a Node tree.
// 	 */

// 	public function RenderTail(): Traversable|\Approach\Render\Stream|string|\Stringable
// 	{
// 		yield null;
// 	}
// }

// /// File: ./approach/Render/Node.php

// <?php

// /**
//  * 
//  * This is the Node class, which is the base class for all Approach\Render objects.
//  * 
//  * @package Approach
//  * @subpackage Render
//  * @version 2.0.-1 beta
//  * 
//  * @license Apache 2.0
//  * @since	2023-02-04
//  * @see	https://orchetrationsyndicate.com/Approach/Render/Node
//  * 
//  * 
//  * @property null|string|Stringable|Stream $content The content of the Node object.
//  * @property bool $prerender A boolean value that determines whether or not the Node object should be prerendered.
//  * @property array $nodes An array of Node objects that are children of the Node object.
//  * @property array $_labeled_nodes An array of Node objects that are children of the Node object.
//  * @property array $_node_labels An array of Node objects that are children of the Node object.
//  * 
//  * @method static Node __set_state(array $properties) This creates a new Node object with properties set from an array.
//  * @method Node __construct(null|string|Stringable|Stream $content = null, bool $prerender = false) This is the constructor for the Node class.
//  * @method void static __static_init() A static initializer that instantiates the null Node, for use in comparisons.
//  * @method Node __set_state(array $properties) This creates a new Node object with properties set from an array.
//  * @static @method Node|nullstate getById(int $id) This function returns a Node object with a specific ID.
//  * 
//  * @uses Approach\Render\Node\Properties
//  * @uses Approach\Render\Node\Streamability
//  */

// namespace Approach\Render;
// use \Approach\nullstate as nullstate;
// use \Approach\Render\Stream;
// use \Stringable;

// /*
// 	TODO: 
// 		- Specialize FatNode out of Node
// 			- add parent and ancestor mechanic to FatNode
// 				- replace downstream references to Node using __render_id with a reference to FatNode
// 				- replace downstream Node classes using a $parent to extend FatNode
// 				- replace downstream Node classes using an $ancestor to extend FatNode
// 			- add a $hyperlink property to FatNode
// 				- hyperlinks are a special type of node that can be used to create links to other nodes
// 				- hyperlinks are not rendered by default
// 				- hyperlinks are similar to <a> tags, but they are not HTML-specific,
// 				  they can be used to create graph-like structures between nodes
// 				- Approach uses this to create the Resource Protocol URLs
// 				  MariaDB://localhost/MyDatabase/MyTable[MyColumn,MyColumn2:0..10,MyColumn3]
// 				  MyAPI://api.example.com/MyEndpoint/Product.image_list@Gallery/Image[src:0..10]
// 				  shared://myproject.share.corp/support/patterns/MyPattern.xml
// 			-

// */

// class Node extends Container
// {
// 	private array $_labeled_nodes = [];	
// 	private array $_node_labels = [];	

// 	public $nodes = [];
// 	public static Node $null;

// 	use Node\Properties;
// 	use Streamability;

// 	public function __construct(public null|string|Stringable|Stream $content = null, public bool $prerender = false)
// 	{
// 		// $this->set_render_id();
// 	}

// 	# an initializer for the statics
// 	/**
// 	 * A static initializer that instantiates the null Node, for use in comparisons.
// 	 */
// 	public static function __static_init()
// 	{
// 		self::$null = new Node();
// 	}

// 	/**
// 	 * This creates a new Node object with properties set from an array.
// 	 * 
// 	 * @param array properties An array of properties that need to be set for the Node object.
// 	 * 
// 	 * @return Node A Node object.
// 	 */
// 	public static function __set_state(array $properties): Node
// 	{
// 		$node = new Node();
// 		if(isset($properties['content']))
// 			$node->content = $properties['content'];
// 		if(isset($properties['prerender']))
// 			$node->prerender = $properties['prerender'];
// 		if(isset($properties['nodes']))
// 			$node->nodes = $properties['nodes'];		
// 		if(isset($properties['_render_id']))
// 			$node->_render_id = $properties['_render_id'];
// 		return $node;
// 	}

// 	/**
// 	 * This recursively searches for a node with a specific ID within a tree structure.
// 	 * 
// 	 * @param root This is a reference to the root node of a tree structure.
// 	 * @param _render_id _render_id is a parameter that represents the unique identifier of a node in a
// 	 * tree structure. The function is designed to search for a node with a specific _render_id and return
// 	 * it if found.
// 	 * 
// 	 * @return Node|nullstate either a Node object or nullstate::null (which is likely a null value).
// 	 */
// 	public static function GetById(&$root, $_render_id): Node|nullstate
// 	{
// 		if ($root->_render_id == $_render_id) return $root;

// 		foreach ($root->children as $child) {
// 			$result = self::GetById($child, $_render_id);

// 			if ($result instanceof self) {
// 				if ($result->_render_id == $_render_id) return $result;
// 			}
// 		}

// 		return nullstate::null;
// 	}
// }

// /// File: ./approach/Render/XML.php

// <?php

// namespace Approach\Render;

// use \Approach\Render;
// use \Approach\Render\Attribute;
// use \Approach\Render\Markup;
// use \Stringable;
// use Traversable;

// class XML extends Render\Node implements Stream
// {                                        // Uses All Markup Traits
//     use Markup\Validation;
//     use Markup\Properties;

//     public function __construct(
//         public null|string|Stringable $tag = NULL,
//         public null|string|Stringable|Stream|self $content = null,
// 		public null|array|Attribute $attributes = new Attribute,
//         public bool $prerender = false
//     ) {
// 		if(is_array($attributes))
// 			$this->attributes = Attribute::fromArray($attributes);
// 		$this->set_render_id();
//     }

//     public function RenderHead(): Traversable
//     {
//         yield
//             $this->before .
//             '<' .
//             $this->tag . $this->attributes .
//             ($this->selfContained ?
//                 ' />' :
//                 '>' . $this->prefix
//             )    //prefix and suffix don't really make sense on  self-contained elements
//         ;            // :before <input value="abc" />  :after
//     }

//     public function RenderCorpus(): Traversable
//     {
//         if (!$this->prerender) {
//             foreach ($this->nodes as $n) {
// 				yield from $n->RenderHead();
// 				yield from $n->RenderCorpus();
// 				yield from $n->RenderTail();

//             }
//             $this->prerender = true;
//         }
// 		yield $this->content;
//     }

//     public function RenderTail(): Traversable
//     {
//         yield ($this->selfContained ?
//             '' :
//             $this->suffix . '</' . $this->tag . '>'
//         ) .
//             $this->after;
//     }

//     public static function GetByTag(&$root, string $tag)
//     {
//         $Store = [];

//         foreach ($root->children as $child)   //Get Head
//         {
//             if ($child->tag == $tag) {
//                 $Store[] = $child;
//             }

//             foreach ($child->children as $children) {
//                 $Store = array_merge($Store, self::GetByTag($children, $tag));
//             }
//         }

//         return $Store;
//     }

//     public static function GetFirstByTag(&$root, string $tag)
//     {

//         return self::GetByTag($root, $tag)[0] ?? null;
//     }
// }

// /// File: ./approach/Render/HTML.php
// <?php

// namespace Approach\Render;

// use \Approach\Render\Node;
// use \Approach\Render\XML;
// use \Approach\Render\Attribute;
// use \Stringable;

// /**
//  * HTML Class Reference
//  * The HTML class extends the XML class and uses the HTML\Properties trait. It represents an HTML element and is used to create and manipulate HTML content in the Approach Rendering System.
//  * 
//  * The HTML class has a number of traits that allow it to define and set various HTML-specific properties, 
//  * such as the tag, id, classes, attributes, etc..
//  * 
//  * Properties
//  * $tag (string|Stringable|null): The name of the HTML tag.
//  * $id (string|Stringable|null): The id attribute of the element.
//  * $classes (string|array|Attribute|null): The class attribute of the element. If a string or array is passed, it will be converted to an Attribute object.
//  * $attributes (array|Attribute|null): Other attributes of the element. If an array is passed, it will be converted to an Attribute object.
//  * $content (string|Stringable|Stream|self|null): The content of the element.
//  * $styles (array): An array of inline style rules.
//  * $prerender (bool): A flag indicating whether the element has been prerendered or not.
//  * $selfContained (bool): A flag indicating whether the element is self-contained or not.
//  * 
//  * Methods
//  * __construct(): The constructor for the HTML class.
//  * 
//  * @package Approach\Render
//  * @version 1.0.0
//  * @since 1.0.0
//  * @see \Approach\Render\Node
//  * @see \Approach\Render\XML
//  * @see \Approach\Render\HTML\Properties
//  * @see \Approach\Render\HTML\Tag
//  * @see \Approach\Render\HTML\ID
//  * @see \Approach\Render\HTML\Classes
//  * @see \Approach\Render\HTML\Attributes
//  * @see \Approach\Render\HTML\Content
//  * @see \Approach\Render\HTML\Styles
//  * @see \Approach\Render\HTML\Prerender
//  * @see \Approach\Render\HTML\SelfContained
//  * 
//  * @license Apache-2.0
//  * @link https://approach.dev
//  * 
//  * @example
//  * 
//  * // Create a new HTML element
//  * $element = new HTML('div');
//  * 
//  * // Set the id attribute
//  * $element->id = 'my-id';
//  * 
//  * // Set the class attribute
//  * $element->classes = ['my-class', 'my-other-class'];
//  * 
//  * // Set the content
//  * $element->content = 'Hello, world!';
//  * 
//  * // Set the style attribute
//  * $element->styles = [
//  *   'color' => 'red',
//  *   'background-color' => 'blue',
//  * ];
//  * 
//  * // Render the element
//  * echo $element->render();
//  * 
//  * // Output:
//  * // <div id="my-id" class="my-class my-other-class" style="color: red; background-color: blue;">Hello, world!</div>
//  * 
//  */


// class HTML extends XML
// {
//     use HTML\Properties;

//     public function __construct(
//         public null|string|Stringable $tag = NULL,
//         public null|string|Stringable $id = null,
// 		null|string|array|Node|Attribute $classes = null,
//         public null|array|Attribute $attributes = new Attribute,
//         public null|string|Stringable|Stream|self $content = null,
//         public array $styles = [],
//         public bool $prerender = false,
//         public bool $selfContained = false,
//     ) {
// 		$this->classes = new Node;
//         if (is_array($classes) || is_string($classes))
// 		{
// 			$classes = is_array($classes) ? $classes : explode(' ', $classes);

// 			foreach($classes as $class){
// 				$this->classes[] = new Node($class);
// 			}
// 		}
// 		elseif($classes instanceof Attribute)
// 			$this->classes[] = $classes;

//         if (is_array($attributes))
//             $this->attributes = Attribute::fromArray($attributes);
//         $this->set_render_id();
//     }
// }

// /// File: ./approach/Render/Node/Keyed.php

// <?php

// namespace Approach\Render\Node;

// use \Approach\Render\Node;
// use \Approach\Render\Associative;
// use \Approach\Render\Stream;
// use \Stringable;
// use Traversable;

// class Keyed extends Node implements \ArrayAccess
// {
//     use Associative;
//     public static $segmentation_phrase    = ' ';
//     public static $associative_phrase     = '=';
//     public static $encapsulating_phrase   = '"';
//     public static $chaining_phrase        = '';


//     public function __construct(
//         public null|string|Stringable|Stream|self $name =  null,
//         public null|string|Stringable|Stream|self $content = null
//     )
//     {
//         $this->_keys[(string)$name] = $this;
//     }

//     public static function fromArray(array $dictionary)
//     {
//         $a = new static();
//         if (\Approach\Approach::isArrayAssociative($dictionary))
//             foreach ($dictionary as $k => $v)
//             {
//                 $a[(string)$k] = $v;
//             }
//         else
//             foreach ($dictionary as $v)
//             {
//                 $a[] = $v;
//             }
//         return $a;
//     }

//     public function &toArray(): array
//     {
//         $a = [];
//         foreach ($this->nodes as $node)
//         {
//             $a = array_merge($a, $node->toArray());
//         }
//         if ($this->name !== null)
//             $a[(string)$this->name] = $this->content;

//         return $a;
//     }

//     public function RenderHead(): Traversable
//     {
// 		if ($this->name === null)
// 			yield '';

//         else yield self::$segmentation_phrase;
//     }

//     /**
//      * Generates a traversable corpus of the node and its descendants.
//      *
//      * @return Traversable
//      */
//     public function RenderCorpus(): Traversable
//     {
//         if ($this->name !== null)
//         {
//             yield (string)$this->name;
//             if ($this->content !== null)
//             {
//                 yield
//                     self::$associative_phrase .
//                     self::$encapsulating_phrase .
//                     $this->content .
//                     self::$encapsulating_phrase;
//             }
//         }

// 		// If the attribute is nameless, but has content, then it is a keyless attribute (may be chained by pushback to nodes)
//         else if ($this->content !== null)
//         {
//             yield $this->content;
//         }

//         yield '';
//     }

//     public function RenderTail(): Traversable
//     {
//         foreach ($this->nodes as $node)
//         {
//             yield
//                 $this->name !== null ?
//                 self::$chaining_phrase
//                 :    '';
//             yield from $node->RenderHead();
//             yield from $node->RenderCorpus();
//             yield from $node->RenderTail();
//         }
//     }
// }

// /// File: ./approach/Render/Attribute.php

// <?php

// namespace Approach\Render;

// class Attribute extends Node\Keyed implements \ArrayAccess
// {
// 	public static $segmentation_phrase=' ';
// 	public static $associative_phrase ='=';
// 	public static $encapsulating_phrase='"';

// }
