<?php
/*
    Resource
    (noun)

    - Something that is available for use - or that can be used for support or help.
    - An available supply that can be drawn on when needed.
    - The ability to deal with a difficult or troublesome situation effectively; resourcefulness.
    - The total means available for infrastructure development, such as mineral wealth, labor force, and armaments.
    - The total means available to a companny for increasing production or profit, including plant, labor, and raw material; assets.
    - Such means considered individually.

    From The American Heritage® Dictionary of the English Language, 5th Edition
*/

namespace Approach\Resource;

use \Approach\nullstate;
use \Approach\Resource\Aspect\aspects;
use \Approach\path;
use \Approach\Resource\Aspect\discover;
use \Approach\Resource\Aspect\Aspect;
use \Approach\Resource\Aspect\location;
use \Approach\Resource\Aspect\operation;
use \Approach\Resource\Aspect\field;
use \Approach\Scope;

use \Approach\Render\Stream;
use \Approach\Render\Node as RenderNode;
use \Approach\Render\Container;
use Approach\Service\connectable;
use \Approach\Service\Service;
use \Approach\Service\flow;
use \Approach\Service\format;
use \Approach\Service\target;
use Stringable;

abstract class accessor
{
}

const locate	= 0;
const pick		= 1;
const sort		= 2;
const weigh		= 3;
const sift		= 4;
const divide	= 5;
const filter	= 6;

const field		= 0;
const feature	= 1;
const operate	= 2;
const quality	= 3;
const quantity	= 4;
const mode		= 5;

class Resource extends RenderNode implements Stream
{
	// TODO: Add a Resource\context class to hold the Aspects
	// Make it extend Resource, so that a context can hold bare resources but still reconfigure results
	// Then work $__approach_resource_context out of the Resource class
	private Aspect $__approach_resource_context;  // intentionally verbose to avoid collisions
	/**
	 * @var $where 
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
		// public $host='',				// The host server, eg localhost, by default the active Scope represents the host
		$where	= '/', 					// The path to the resource, eg /path/to/resource, by default loads the root of the host
		$pick 	= null,					// Constraints on the resource selection
		$sort 	= null,					// Define the result ordering
		$weigh 	= null,					// Augment sorting with weights
		$sift 	= null,					// Partition and add criteria to the result set
		$divide = null,					// Divide the result set into groups
		$filter = null 					// Apply post-processing filters to the result set
	) {
		/** Alter resource selection via Aspects	*/
		$this->__approach_resource_context = new Aspect();
		$this->__approach_resource_context['locate']	= ($where 	?? new RenderNode(content: '/'));
		$this->__approach_resource_context['pick']		= ($pick 	?? new Container());
		$this->__approach_resource_context['sort']		= ($sort 	?? new Container());
		$this->__approach_resource_context['weigh']		= ($weigh 	?? new Container());
		$this->__approach_resource_context['sift']		= ($sift 	?? new Container());
		$this->__approach_resource_context['divide']	= ($divide ?? new Container());
		$this->__approach_resource_context['filter']	= ($filter ?? new Container());
	}

	public function define()
	{

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
	): Resource|Stringable|string|nullstate {
		// Initialize values here so things don't persist between consecutive calls & failures
		$this->tmp_parsed_url = array();
		$tmp_parsed_url = array();


		// check protocl exists, and parse it
		if (strpos($where, '://') === false) {
			return nullstate::ambiguous;
		}

		list($tmp_parsed_url['protocol'], $where) = explode('://', $where, 2);

		if ($tmp_parsed_url['protocol'] === '' || empty($where[0]) || $where[0] === '/') {
			return nullstate::ambiguous;
		}

		if (strpos($where, '?') !== false) {
			list($where, $tmp_parsed_url['query_string']) = explode('?', $where, 2);

			// parse_str stores the result in the second argument, and urldecodes automatically
			// RFC 3986 section 3.4 doesn't elaborate much on query strings, but I will assume
			// that this function follows the spec
			parse_str($tmp_parsed_url['query_string'], $tmp_parsed_url['query_string']);
		} else {
			$tmp_parsed_url['query_string'] = [];
		}

		if (strpos($where, '/') === false) {
			$tmp_parsed_url['host'] = $where;
			$tmp_parsed_url['parts'] = [];
		} else {
			list($tmp_parsed_url['host'], $where) = explode('/', $where, 2);
			$tmp_parsed_url['parts'] = array_values(array_filter(explode('/', $where)));
		}

		foreach ($tmp_parsed_url['parts'] as $key => $part) {
			$parsed_part = [
				'type' => null,
				'criterias' => [],
				'parsed_csv' => null,
				'sub_delim_part' => null
			];

			// Get sub delim if present
			if (strpos($part, ';') !== false) {
				list($part, $parsed_part['sub_delim_part']) = explode(';', $part, 2);
			}

			// if there is (...), parse the CSV input
			$first_opening_parenthesis = strpos($part, '(');
			if ($first_opening_parenthesis !== false) {
				$length = strlen($part);

				if ($length < 2 || empty($part[$length - 1]) || $part[$length - 1] !== ')') {
					return nullstate::ambiguous;
				}

				$parsed_part['parsed_csv'] = str_getcsv(substr($part, $first_opening_parhenthesis + 1, $length - $first_opening_parenthesis - 1));
				$part = substr($part, 0, $first_opening_parenthesis);

				if ($parsed_part['parsed_csv'] === [] || $parsed_part['parsed_csv'] === [null]) {
					return nullstate::ambiguous;
				}
			}

			// If there is no [, there's nothing else to do
			if (strpos($part, '[') === false) {
				if ($parsed_part['parsed_csv'] !== null) {
					return nullstate::ambiguous;
				}

				$parsed_part['type'] = $part;
				$tmp_parsed_url['parts'][$key] = $parsed_part;
				continue;
			}

			// Otherwise, parse the [...] blocks
			list($parsed_part['type'], $part) = explode('[', $part, 2);

			// Since we removed the opening [, also remove the closing ]
			if (substr($part, -1) !== ']') {
				return nullstate::ambiguous;
			}

			$part = substr($part, 0, -1);

			for (
				$i = 0,
				$part_max_length = strlen($part);

				$part !== '';

				$part = substr($part, $i),
				$part_max_length = strlen($part),
				$i = 0
			) {
				// First, check if we're at the end of the criteria block
				if ($part[0] === ']') {
					if ($part_max_length === 1 || $part[1] !== '[') {
						return nullstate::ambiguous;
					}

					$parsed_part['criterias'][] = [
						'type' => 'next_block',
						'token' => ']['
					];

					$i += 2;

					continue;
				}

				// Second, get through any white space
				for (;
					$i < $part_max_length
						&& match ($part[$i]) {
							' ', "\r", "\n" => true,
							default => false
						};
					$i++
				);

				if ($i !== 0) {
					$parsed_part['criterias'][] = [
						'type' => 'whitespace',
						'token' => substr($part, 0, $i)
					];

					continue;
				}

				// Next, try matching a string
				if ($part[$i] === '"' || $part[$i] === "'") {
					$start_i = $i;

					for ($i++; $i < $part_max_length && $part[$i] !== $part[$start_i]; $i++);

					$i++;

					$parsed_part['criterias'][] = [
						'type' => 'string',
						'token' => substr($part, $start_i, $i)
					];

					continue;
				}

				// Next, try to match a number
				for (;
					$i < $part_max_length
						&& $part[$i] >= '0'
						&& $part[$i] <= '9';
					$i++
				);

				if (
					$i > 0
					&& (
						$i === $part_max_length
						|| $part[$i] === ']'
						|| $part[$i] === ','
					)
				) {
					$parsed_part['criterias'][] = [
						'type' => 'int',
						'token' => intval(substr($part, 0, $i))
					];

					continue;
				}

				// If the number ends with a - and $i is 2, maybe it's a date?
				if (
					$i === 2
					&& $i + 10 <= $part_max_length
					&& $part[2] === '-'
					&& ($part[3] >= '0' && $part[3] <= '9')
					&& ($part[4] >= '0' && $part[4] <= '9')
					&& $part[5] === '-'
					&& ($part[6] >= '0' && $part[6] <= '9')
					&& ($part[7] >= '0' && $part[7] <= '9')
					&& ($part[8] >= '0' && $part[8] <= '9')
					&& ($part[9] >= '0' && $part[9] <= '9')
				) {
					$parsed_part['criterias'][] = [
						'type' => 'date',
						'token' => substr($part, 0, 10)
					];

					$i = 10;
					continue;
				}

				// If the number ends with a dot, maybe it's a range?
				if (
					$i > 0
					&& $i !== $part_max_length
					&& $part[$i] === '.'
					&& $i + 2 <= $part_max_length
					&& $part[$i + 1] === '.'
				) {
					$i += 2;

					for (;
						$i < $part_max_length
							&& $part[$i] >= '0'
							&& $part[$i] <= '9';
						$i++
					);

					$parsed_part['criterias'][] = [
						'type' => 'range',
						'token' => substr($part, 0, $i)
					];

					continue;
				}

				// If the number doesn't end with a ], assume it's part of an identifier?
				for (;
					$i < $part_max_length
						&& (
							(
								$part[$i] >= '0'
								&& $part[$i] <= '9'
							)
							|| (
								$part[$i] >= 'a'
								&& $part[$i] <= 'z'
							)
							|| (
								$part[$i] >= 'A'
								&& $part[$i] <= 'Z'
							)
							|| $part[$i] === '_'
							|| $part[$i] === '-'
							|| $part[$i] === '.'
						);
					$i++
				);

				if ($i !== 0) {
					$parsed_part['criterias'][] = [
						'type' => 'identifier',
						'token' => substr($part, 0, $i)
					];

					continue;
				}

				// match <= and >= and == and !=
				if ($part_max_length >= 2 && $part[1] === '=') {
					switch ($part[0]) {
						case '>':
							$parsed_part['criterias'][] = [
								'type' => 'greater_equal_to',
								'token' => '>='
							];

							$i += 2;
							continue 2;

						case '<':
							$parsed_part['criterias'][] = [
								'type' => 'less_equal_to',
								'token' => '<='
							];

							$i += 2;
							continue 2;

						case '=':
							$parsed_part['criterias'][] = [
								'type' => 'equal_to',
								'token' => '=='
							];

							$i += 2;
							continue 2;

						case '!':
							$parsed_part['criterias'][] = [
								'type' => 'not_equal_to',
								'token' => '!='
							];

							$i += 2;
							continue 2;
					}
				}

				// Match , and : and < and >
				switch ($part[0]) {
					case ',':
						$parsed_part['criterias'][] = [
							'type' => 'comma',
							'token' => ','
						];

						$i++;
						continue 2;

					case ':':
						$parsed_part['criterias'][] = [
							'type' => 'colon',
							'token' => ':'
						];

						$i++;
						continue 2;

					case '>':
						$parsed_part['criterias'][] = [
							'type' => 'greater_than',
							'token' => '>'
						];

						$i++;
						continue 2;

					case '<':
						$parsed_part['criterias'][] = [
							'type' => 'less_than',
							'token' => '<'
						];

						$i++;
						continue 2;
				}

				// Finally, if it's matched noething so far... It's probably a parsing error
				return nullstate::ambiguous;
			}

			$tmp_parsed_url['parts'][$key] = $parsed_part;
		}

		$this->tmp_parsed_url = $tmp_parsed_url;

		return $this;
	}

	public function sort(\Stringable|string|Aspect $by, bool $ascending = true)
	{
		if (!($by instanceof Aspect))
			$by = new field(discover::field, content: $by);

		$by[quality] = $by[quality] ?? $ascending;
		$this->aspects[sort]->nodes[$by->label] = $by;
	}

	public function weigh(field|Aspect $by, int $weight = 1)
	{
		$by[quantity] = $by[quantity] ?? $weight;
		$this->aspects[sort]->nodes[]	= $by;
	}
	public function pick(Aspect $by)
	{
		$this->aspects[pick]->nodes[]	= $by;
	}
	public function sift(Aspect $by)
	{
		$this->aspects[sift]->nodes[]	= $by;
	}
	public function divide(Aspect $by)
	{
		$this->aspects[divide]->nodes[]	= $by;
	}
	public function filter(Aspect $by)
	{
		$this->aspects[filter]->nodes[]	= $by;
	}

	// Use Service's Encode/Decode classes to convert $this to $type
	public function as(RenderNode $type)
	{
		$typename = $type::class || $type->__toString();
		$service = new Service(
			\Approach\Service\flow::in,
			format_in: format::mysql,
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
	public function load($service, RenderNode $source)
	{
		return $service->payload;
	}

	/**
	 * Should ideally return type-safe true or false
	 *
	 * @param \Approach\Render\KeyedNode $exchange Any compatible payload container, ideally and object of type ExchangeTransport
	 * @param \Approach\Render\Node $type                                                                                                                                                                                                                                                                                               
	 * @access public
	 */
	public function save($exchange, RenderNode $type)
	{
		return false;
	}


	/**
	 * Mint a resource class file
	 * 
	 * TODO: Use Imprint & Patterns instead
	 *      - Requires Loop node
	 *      - Even better if made as Components in a Composition
	 *      - When both are done
	 *          - make these arguments into a new Render\Node format
	 *          - add Decoder and Encoder for Services to exchange  Render\ClassMetadata with Resource\Type
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
	): void {
		// If the file does not exist, then build it
		if (!file_exists($path) || $overwrite) {
			// Grab the last part of the class name for the label
			$class = explode('\\', $class);
			$class = $class[count($class) - 1];

			$extends = $extends ?? '\Approach\Resource\MariaDB\Server';
			$namespace = $namespace ?? \Approach\Scope::$Active->project . '\Resource';
			$uses = $uses ?? [static::class,];

			$content = '<?php ' . PHP_EOL . PHP_EOL;

			// Write the namespace
			$content .= 'namespace ' . $namespace . ';' . PHP_EOL . PHP_EOL;

			foreach ($uses as $use)	$content .= 'use ' . $use . ';' . PHP_EOL;
            $profilePath = $namespace;
            // make it into \Resource\Aspect\MariaDB
            $profilePath = str_replace('\\Resource\\MariaDB', '\\Resource\\MariaDB\\Aspect', $profilePath);

            $content .= 'use ' . $profilePath . '\\' . $class . '_user_trait;' . PHP_EOL . PHP_EOL;

			// Write the class
			$content .= 'class ' . $class . ' extends ' . $extends . '{' . PHP_EOL;
			$content .= "\t" . '// Change the ' . $class . '_user_trait to add functionality to this generated class' . PHP_EOL;
            //FIXME: Uncomment this and change the order so that user_trait is included first
//			$content .= "\t" . 'use ' . $class . '_user_trait;' . PHP_EOL . PHP_EOL;
			foreach ($constants as $constant)	$content .= "\t" . 'const ' . $constant . ';' . PHP_EOL;
			foreach ($properties as $property)	$content .= "\t" . 'public ' . $property . ';' . PHP_EOL;
			foreach ($methods as $method)		$content .= "\t" . $method . PHP_EOL;
			$content .= '}' . PHP_EOL;

			$file_dir = dirname($path);
            $profileFileDir = str_replace('Resource/MariaDB', 'Resource/MariaDB/Aspect', $file_dir);

//            $namespacePath = $profilePath . '\\'.$class;

			// Make sure the path/ and path/user_trait.php exist
			if (!file_exists($file_dir)) mkdir($file_dir, 0770, true);
			if (!file_exists($file_dir . '/' . $class . '_user_trait.php')) {
				$user_trait =
					'<?php

namespace ' . $profilePath . ';

// use ' . $profilePath . ';

trait ' . $class . '_user_trait
{
	//use profile;
	/**** User Trait ****
	 * 
	 *  This trait is used to add user functionality to an Approach Resource.
	 * 
	 *  Anything you add here will be available to the primary resource of
	 *  this namespace. 
	 * 
	 *  This is a good place to use hooks and/or override methods to achieve
	 *  desired functionality.
	 * 
	 *  Examples include: 
	 *    - Adding a user_id property
	 *    - Changing the behavior of the load() or save() method
	 *    - Adding behavior with preload(), onsave(), postpush(), onpull(), preacquire(), etc..
	 *    - Adding functions that work with your custom operations and aspects
	 *    - Tieing into the map system deeper
	 * 
	 * This trait is automatically included in the class that is generated, so
	 * you can use it immediately. This file is here for your convenience
	 * and will not be overwritten by the generator.
	 * 
	 */
}';
                if (!is_dir($profileFileDir)) {
                    mkdir($profileFileDir, 0777, true);
                }
				$file = fopen($profileFileDir . '/' . $class . '_user_trait.php', 'w');
				fwrite($file, $user_trait);
				fclose($file);
			}

			$file = fopen($path, 'w');
			fwrite($file, $content);
			fclose($file);
		}
	}

	/**
	 * Scan the following directories for resources:
	 *     - path::get(path::installed) . '/Resource' and all subdirectories
	 *     - path::get(path::project) . '/Resource' and all subdirectories
	 *     - In both cases, ignore the following directories:
	 *          - ../Resource/wild
	 *          - ../Resource/vendor
	 *          - ../Resource/community
	 *          - ../Resource/extension
	 *          - ../Resource/test
	 *          - TODO: make scanning these configurable
	 * 
	 * If a PHP file is found, check if that name is a class that extends Resource
	 * If so, call the method discover() on that class
	 * 
	 */
	public function discover()
	{

		$paths = [
			'approach' => path::installed->get() . '/Resource',
			'project' => path::project->get() . '/Resource',
		];
		$ignore = [
			'wild',
			'vendor',
			'community',
			'extension',
			'test',
		];

		// We don't want to pollute the child classes with methods that are not
		// intended to be used by the end user. So we will use a closure to check
		// part of the path directly following /Resource/ against $ignore[]
		$check_criteria = function (string|Stringable $path) {
			$rejection = false;
			$roots = [
				path::installed->get() . '/Resource',
				path::project->get() . '/Resource',
			];
			$ignore = [
				'wild',
				'vendor',
				'community',
				'extension',
				'test',
			];

			$path = (string) $path;
			$path = explode('/', $path);

			// Get the index following /Resource/ but after $roots[]
			$index = 0;
			$root_matched = false;
			$root_length = 0;
			$which_root = null;
			foreach ($roots as $rootpath) {
				// check  //my/filesystem/company/Resource/not-this/project/src/Resource/[*** this ***]/is/Resource/[not this]/we/want
				// Make sure we are aligned with the root
				$root = explode('/', $rootpath);
				$root_length = count($root);

				// If the root is longer than the path, then we are not in a root directory
				if ($root_length > count($path)) continue;


				// Check if the root matches the path until the root length
				for ($i = 0; $i < $root_length; $i++) {
					if ( // If path does not line up with root
						$path[$i] !== $root[$i] || (
							// or root/Resource 
							$path[$i] === 'Resource' && $path[$i - 1] === end($root)
						)
					) {
						// Reject the path
						$index = 0;
						$which_root = null;
						break;
					}

					$index = $i + 1;
					$which_root = $rootpath;
				}

				if ($index > 0) {
					$root_matched = true;
					break;
				}
			}



			// If the index is not found, then we are not in a root directory
			if ($root_matched) return false;

			// If the index is found, then check if the next index is in $ignore
			if (in_array($path[$index], $ignore)) return false;

			if (!$which_root) return false;

			// If we made it this far, then we are not in an ignored directory
			return $which_root;
		};

		foreach ($paths as $which => $path) {
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

			// Bail out fast if the path does not exist by checking multiple skip criteria
			foreach ($files as $file) {
				if ($file->isDir()) continue;
				if ($file->getExtension() !== 'php') continue;

				$pathname = $file->getPathname();

				// Check if the path is in a valid directory
				if (!$check_criteria($pathname)) continue;

				// Move the cursor to the end of the root directory
				$cursor = strpos($pathname, $path);
				if (!$cursor) continue;                          // If root is not found, then invalid path

				// Get the first occurrence of /Resource/ after the root
				$cursor = strpos($pathname, '/Resource/', $cursor);
				if (!$cursor) continue;                          // If /Resource/ is not found, then invalid path

				// If /Resource/ is found, then start after it's last occurrence
				$cursor += 10;

				// Get the path after /Resource/ and before .php
				$possible = substr($pathname, $cursor, -4);

				// normalize path Windows/Mac/.. to Linux
				$possible = str_replace('\\', '/', $possible);

				// oddly, now we have to reverse that process to go to PSR-4
				// making $possible the class name, possibly
				$possible = str_replace('/', '\\', $possible);

				$prefix = '';
				if ($which === 'approach') {
					$prefix = '\\Approach\\Resource\\';
				} elseif ($which === 'project') {
					$prefix = '\\' . Scope::$Active->project . '\\Resource\\';
				}

				$possible = $prefix . $possible;

				// Check if the class exists
				if (!class_exists($possible)) continue;

				// Check if the class extends Resource
				if (is_subclass_of($possible, Resource::class)) {
					// Call the static method discover() on that class
					try {
						$possible::discover();
					} catch (\Throwable $e) {
						echo 'Class instantiation failed: ' . $possible . PHP_EOL . $e->getMessage() . PHP_EOL;
					}
				}

				// Otherwise do nothing, not a resource
			}
		}
	}

	public static function get_aspect_directory()
	{
		$class = explode( 		// Split the string in an array
			'\\', 				// Define the separator
			static::class	 	// Get the class name
		);
		$class = end($class);	// Get the last part of the class name

		// Get the directory of the class
		$aspect_directory = dirname(								// Get the directory of the file
			(new \ReflectionClass(static::class))->getFileName()	// Get the file name of the class
		);

		$aspect_directory .= '/' . $class . '/';				// Add the aspects directory to the path

		// Are we on Windows?
		$is_windows = (strtolower(substr(PHP_OS, 0, 3)) === 'win');

		// If so, then we need to convert the path to Windows format
		if ($is_windows) $aspect_directory = str_replace('/', '\\', $aspect_directory);

		// Make sure the path exists, recursively
		if (!file_exists($aspect_directory)) mkdir($aspect_directory, 0770, true);

		return $aspect_directory;
	}

	/**
	 * Tell autoloaders about the new class
	 * 
	 * @param string $resource_root The root namespace of the resource, eg 'Approach\Resource'
	 * @param string $resource_class_path The path to the class file, eg 'Resource'
	 * @access public
	 * @static
	 */
	protected static function __update_composer_autoloader(
		string $resource_root = null,
		string $resource_class = null
	) {
		$resource_root = $resource_root ?? path::resource->get();
		$resource_ns = '\\' . Scope::$Active->project . '\\Resource';
		$classname = $resource_ns . '\\' . $resource_class;

		spl_autoload_register(function ($classname) use ($resource_root, $resource_class) {
			global $spl_counter;
			$resource_class = str_replace('\\', '/', $resource_class);
			$resource_class = $resource_root . '/' . $resource_class . '.php';

			if (file_exists($resource_class)) {
				require_once $resource_class;
			}
		});
	}
}
