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
use \Approach\Service\Service;

class Cache extends RenderNode\Keyed{}

abstract class accessor{}

interface accessible
{
    public function access(accessor $by);
}

trait accessibility{
    public function access(accessor $by){
        $results=[];
        return $results;
    }
}


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

class Resource extends RenderNode implements accessible, Stream
{
    use accessibility;

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
	public static array $source=[];
    
	public function __construct(
		public $proto='approach:/',							// The protocol, eg http:, by default approach:/ signifies a resource in the active project
		public $host='',									// The host server, eg localhost, by default the active Scope represents the host
		public $where='/', 									// The path to the resource, eg /path/to/resource, by default loads the root of the host
		Aspect $pick = new Aspect(aspects::field, null),	// Constraints on the resource selection
		Aspect $sort = new Aspect(aspects::field, null),	// Define the result ordering
		Aspect $weigh = new Aspect(aspects::field, null),	// Augment sorting with weights
		Aspect $sift = new Aspect(aspects::field, null),	// Rej
		Aspect $divide = new Aspect(aspects::field, null),	// Divide the result set into groups
		Aspect $filter = new Aspect(aspects::field, null),	// Apply post-processing filters to the result set
	)
	{
		self::RegisterSources([$proto]);

		/** Alter resource selection via Aspects*/
		$this->__selection = [
			pick		=> ($pick ?? new Aspect(aspects::field, ...$pick)),
			sort		=> ($sort ?? new Aspect(aspects::field, null)),
			weigh		=> ($weigh ?? new Aspect(aspects::field, null)),
			sift		=> ($sift ?? new Aspect(aspects::field, null)),
			divide		=> ($divide ?? new Aspect(aspects::field, null)),
			filter		=> ($filter ?? new Aspect(aspects::field, null))
		];
	}

	public static function RegisterSources(Service|array $source)
	{
		if ($source instanceof Service) 
		{
			self::$source[] = new Service();
		}
		if (is_array($source)) 
		{
			foreach ($source as $s) 
			{
				$service_name='';
				if (is_string($s))
				{
					$service_name = $s;
					$s = new $service_name();
				}
				if ($s instanceof Service) 
				{
					self::$source[] = $s;
					$service_name = get_class($s);
				}
				else 
				{
					throw new \Exception('Resource\\'.static::class.' - Invalid source '.$service_name.': '.PHP_EOL. var_export($s, true));
				}
			}
		}
		if (empty(self::$source)) 
		{
			throw new \Exception('No source defined for Resource');
		}
	}
	private static function PrimeConnection()
	{
		foreach(self::$source as $source)
		{
			$connection = $source->connect();
			if(!($connection instanceof accessible))
			{
				throw new \Exception('Connection is not accessible');
			}
		}
		return nullstate::ambiguous;
	}

	public function find(
		string $where 		= '/',
		?Aspect $sort		= null,
		?Aspect $weigh		= null,
		?Aspect $pick		= null,
		?Aspect $sift		= null,
		?Aspect $divide		= null,
		?callable $filter	= null,
		?string $as			= null
	):Resource|accessible|nullstate
	{

		return nullstate::ambiguous;
	}

	public function sort(\Stringable|string|field|Aspect $by, bool $ascending = true){
		if(is_string($by) || $by instanceof \Stringable)
			$by = new field(aspects::field, $by );

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
	public function as(RenderNode $type){
		$caster = 'from_'.self::class;
		return $type::$caster($this);
	}


	/**
	 * Should always return $exchange->payload
	 *
	 * @param \Approach\Render\KeyedNode $service Any compatible payload container, ideally an object of type Service
	 * @param \Approach\Render\Node $source Any object, string, id, etc.. representing the sort of formatted resource to be loaded
	 * @return \Approach\Render\Node $result Any object, string, id, etc.. representing the sort of formatted resource to be loaded
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
}




/*	Harvested Formats
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