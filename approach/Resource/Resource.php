<?php
/*
    Resource
    (noun)

    - Something that is available for use - or that can be used for support or help.
    - An available supply that can be drawn on when needed.
    - The ability to deal with a difficult or troublesome situation effectively; resourcefulness.
    - The total means available for infrastructure development, such as mineral wealth, labor force, and armaments.
    - The total means available to a company for increasing production or profit, including plant, labor, and raw material; assets.
    - Such means considered individually.

    From The American Heritage® Dictionary of the English Language, 5th Edition
*/

namespace Approach\Resource;

use \Approach\nullstate;
use \Approach\Resource\Aspect\aspects;
use \Approach\Resource\Aspect\Aspect;
use \Approach\Resource\Aspect\location;
use \Approach\Resource\Aspect\operation;
use \Approach\Resource\Aspect\field;

use \Approach\Render\Stream;
use \Approach\Render\Node as RenderNode;
use Approach\Service\connectable;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;
use Stringable;

class Cache extends RenderNode\Keyed{}

abstract class accessor{}


const sort		= 0;
const weigh		= 1;
const pick		= 2;
const sift		= 3;
const divide	= 4;
const filter	= 5;

const field		= 0;
const feature	= 1;
const operate	= 2;
const quality	= 3;
const quantity	= 4;
const mode		= 5;

class Resource extends RenderNode implements Stream
{
	protected Aspect $__selection;

	/**
	 * @var array $source 
	 * 
	 * Services are past as an array of URI strings, or as a Service object with 1 or more nodes.
	 * Viable sources for the resource:
	 * - Begin with a protocol, such as "http://" 
	 * - Protocol is a class name, such as "Approach\Resource\MySQL"
	 * - The protocol class must implement the "Approach\Resource\accessible" interface
	 * - The protocol is followed by a host, such as "localhost" or "192.168.0.1" etc...
	 * - The host may or may not be followed by a path, such as "/path/to/resource"
	 * 
	 * The URI may represent 
	 * - A protocol://host combination as a Resource root aka Service
	 * - A generic API Service such as a database connection
	 * - A table, query or other nested / selectable / commandable resource
	 * - A local or network path to a rendering of one or more specific Resource(s)
	 * - Presentation Service
	 * - Generic Approach Services
	 * 
	 */

	public function __construct(
		// public $host='',									// The host server, eg localhost, by default the active Scope represents the host
		public $where='/', 									// The path to the resource, eg /path/to/resource, by default loads the root of the host
		Aspect $pick 	= new Aspect(aspects::container),	// Constraints on the resource selection
		Aspect $sort 	= new Aspect(aspects::container),	// Define the result ordering
		Aspect $weigh 	= new Aspect(aspects::container),	// Augment sorting with weights
		Aspect $sift 	= new Aspect(aspects::container),	// Partition and add criteria to the result set
		Aspect $divide 	= new Aspect(aspects::container),	// Divide the result set into groups
		Aspect $filter 	= new Aspect(aspects::container),	// Apply post-processing filters to the result set
	)
	{
		/** Alter resource selection via Aspects*/
		$this->__selection = [
			pick		=> ($pick 	?? new Aspect(aspects::container)),
			sort		=> ($sort 	?? new Aspect(aspects::container)),
			weigh		=> ($weigh 	?? new Aspect(aspects::container)),
			sift		=> ($sift 	?? new Aspect(aspects::container)),
			divide		=> ($divide ?? new Aspect(aspects::container)),
			filter		=> ($filter ?? new Aspect(aspects::container))
		];
	}

	public function define(){
		
		$aspects = aspects::manifest($this);

		// $this->aspects = $aspects;
		// $this->aspects[aspects::container]->nodes = $aspects;
		// $this->aspects[aspects::container]->nodes[aspects::container]->nodes = $aspects;
		// $this->aspects[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes = $aspects;
		// $this->aspects[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes = $aspects;
		// $this->aspects[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes[aspects::container]->nodes = $aspects;
		// $this->aspects[aspects::container]->nodes[aspect
	}

	public function find(
		string $where 		= '/',
		?Aspect $sort		= null,
		?Aspect $weigh		= null,
		?Aspect $pick		= null,
		?Aspect $sift		= null,
		?Aspect $divide		= null,
		?Aspect $what		= null,
		?callable $filter	= null,
		?string $as			= null
	):Resource|Stringable|string|nullstate
	{

		return nullstate::ambiguous;
	}

	public function sort(\Stringable|string|field|Aspect $by, bool $ascending = true){
		if( !($by instanceof Aspect) )
			$by = new field(aspects::field, $by->__toString() );

		$by[quality] = $by[quality] ?? $ascending;
		$this->aspects[sort]->nodes[ $by->label ] = $by;
	}
	
	public function weigh(field|Aspect $by, int $weight = 1){
		$by[quantity] = $by[quantity] ?? $weight;
		$this->aspects[sort]->nodes[]	= $by;
	}
	public function pick(Aspect $by){
		$this->aspects[pick]->nodes[]	= $by;
	}
	public function sift(Aspect $by){
		$this->aspects[sift]->nodes[]	= $by;
	}
	public function divide(Aspect $by){
		$this->aspects[divide]->nodes[]	= $by;
	}
	public function filter(Aspect $by){
		$this->aspects[filter]->nodes[]	= $by;
	}

	// Use Service's Encode/Decode classes to convert $this to $type
	public function as(RenderNode $type){
		$typename = $type::class || $type->__toString();
		$service = new Service(
			\Approach\Service\flow::in, 
			format_in: format::custom,
			format_out: format::$$typename,
			target_in: target::resource,
			target_out: target::variable,
			input: [$this]
		);
		return $service->payload;

	}

	// Mimic preg_match('/^(\d+\.\.)(\d+)$/', $value, $matches) with strpos()

	/**
	 * Should always return a Service payload
	 *
	 * @param \Approach\Render\KeyedNode $service Any compatible payload container, ideally an object of type Service
	 * @param \Approach\Render\Node $source Any object, string, id, etc.. representing the sort of formatted resource to be loaded
	 * @return array The payload of the service
	 * @access public
	 */
	public function load($service, RenderNode $source){
		return $service->payload;
	}

	/**
	 * Should ideally return type-safe true or false
	 *
	 * @param \Approach\Render\KeyedNode $exchange Any compatible payload container, ideally and object of type ExchangeTransport
	 * @param \Approach\Render\Node $type                                                                                                                                                                                                                                                                                               
	 * @access public
	 */
	public function save($exchange, RenderNode $type){
		return false;
	}


	/**
	 * Mint a resource class file
	 */
	public function MintResourceClass(
		string $path,
		string $class,
		string $extends,
		string $namespace,
		array $uses = [],
		array $constants = [],
		array $properties = [],
		array $methods = [],
		$overwrite = false
	): void
	{
		// If the file does not exist, then build it
		if (!file_exists($path) || $overwrite)
		{
			// Grab the last part of the class name for the label
			$class = explode('\\', $class);
			$class = $class[count($class) - 1];

			$extends = $extends ?? '\Approach\Resource\MariaDB\Server';
			$namespace = $namespace ?? \Approach\Scope::$Active->project . '\Resource';
			$uses = $uses ?? [ static::class, ];

			$content = '<?php ' . PHP_EOL . PHP_EOL;

			// Write the namespace
			$content .= 'namespace ' . $namespace . ';' . PHP_EOL . PHP_EOL;
			foreach ($uses as $use)	$content .= 'use ' . $use . ';' . PHP_EOL;

			// Write the class
			$content .= 'class ' . $class . ' extends ' . $extends . '{' . PHP_EOL;
			foreach ($constants as $constant)	$content .= 'const ' . $constant . ';' . PHP_EOL;
			foreach ($properties as $property)	$content .= 'public ' . $property . ';' . PHP_EOL;
			foreach ($methods as $method)		$content .= $method . PHP_EOL;
			$content .= '}' . PHP_EOL;

			$file = fopen($path, 'w');
			fwrite($file, $content);
			fclose($file);
		}
	}
}




/* Various Formats
	 * 		- JSON
	 * 		- YAML
	 * 		- CSV
	 * 		- XML
	 * 		- HTML
	 * 		- PHP
	 * 		- JavaScript
	 * 		- CSS
	 * 		- LESS
	 * 		- SCSS
	 * 		- SASS
	 * 		- Stylus
	 * 		- CoffeeScript
	 * 		- TypeScript
	 * 		- Markdown
	 * 		- Textile
	 * 		- reStructuredText
	 * 		- DocBook
	 * 		- MediaWiki
	 * 		- DokuWiki
	 * 		- Creole
	 * 		- BBCode
	 * 		- OPML
	 * 		- RSS
	 * 		- Atom
	 * 		- RDF
	 * 		- JSON-LD
	 * 		- Turtle
	 * 		- N-Triples
	 * 		- N-Quads
	 * 		- TriG
	 * 		- N3
	 * 		- TriX
	 * 		- CSV
	 * 		- TSV
*/