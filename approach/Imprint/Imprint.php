<?php

namespace Approach\Imprint;

use Approach\nullstate;
use Approach\path;
use Approach\Render;
use Approach\Render\Attribute;
use Approach\Render\HTML;
use Approach\Render\Node;
use Approach\Render\Stream;
use Approach\Render\Token;
use Approach\Render\XML;
use Approach\Scope;
use SimpleXMLElement;


const ds = DIRECTORY_SEPARATOR;

class Imprint extends Render\Node\Keyed
{
    const TOKEN_SYMBOL_START = '[@ ';
    const TOKEN_SYMBOL_END = ' @]';

    public array $tokens = [];
	public array $found_tokens = [];
    public string $imprint_dir;

    protected array $_used_symbols = [];
    protected array $_bound = [];

    protected $register_token = [];
    protected $generation_count = [];
    protected $resolved_symbols = [];

    public function __construct(public null|array|\Traversable $pattern = null, public ?string $imprint = null, ?string $imprint_dir = null)
    {
        $this->pattern = $pattern ?? [];
        $this->imprint_dir = $imprint_dir ?? Scope::$Active->GetPath(path::imprint);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function __invoke()
    {
        return $this->render();
    }

    /**
     * NOTICE: This is a temporary solution to the problem of creating a new instance of an \Approach\Render\ class from a string.
     * 
     * new ReflectionMethod( $render_type, '__construct' )->invokeArgs( $r, $args );
     * should probably be used as a better temp fix
     * 
     * This function, Imprint::createNodeFromSimpleXML(), will still need to map standard property names to the correct trait property names.
     * 
     * It's fine to have this here, as it ultimately is a function of the Imprint concept.
     * Imprints are stored in XML format, so XML and HTML are the only two render types that need to support special cases.
     * For other render types the attributes are simply interpreted as a dictionary of constructor arguments.
     * 
     * This way, if a user implements a complex render type, they can simply extend the Imprint class:
     * - overload this function to handle their custom render type
     * - calling the parent function to get the default behavior for built-in render types
     * - usually won't be necessary, as the default behavior is to simply pass the attributes as constructor arguments
     */

    protected function createNodeFromSimpleXML(string $render_type, \simpleXMLElement $element): Stream | Node
    {

        // if the element has Render:type or is a node with Render:myType, then override render_type with myType
        if ($element->attributes('render', true)->type) {
            $render_type = $this->getRenderTypeFromElement($element);
        }

        $args = [];
        $classes = [];
        $id = null;

        // handle attributes
        $attributesArray = self::extractAttributes($element);
        $imprintAttributes = self::extractAttributes($element, 'Imprint', true);
        // $renderAttributes = self::extractAttributes($element, 'render', true);

        foreach ($attributesArray as $key => &$value) {
            // Move class and id attributes to their respective properties so they aren't duplicated as XML attributes
            if ($key === 'id' && is_a($render_type, XML::class, true)) {
                $id = $value;
                unset($attributesArray[$key]);
            }
            // Move class and id attributes to their respective properties so they aren't duplicated as XML attributes
            if ($key === 'class' && is_a($render_type, HTML::class, true)) {
                $value = $this->hasToken($value) ? $this->stringableToToken($value, true) : $value;
                $classes = [...$classes, $value];
                unset($attributesArray[$key]);
            }
        }

        $classes = Attribute::fromArray($classes);
        $attributes = Attribute::fromArray($attributesArray);

        foreach ($attributes->nodes as &$attr) {
            $attr->name =
                $this->hasToken($attr->name . '') ?
                $this->stringableToToken($attr->name . '', true) : $attr->name;

            $attr->content =
                $this->hasToken($attr->content . '') ?
                $this->stringableToToken($attr->content . '', true) : $attr->content;
        }
        // end handle attributes

        switch ($render_type) {
            case '\\' . HTML::class:
                // Text node simpleXMLElement, treat as content
                if ($element->nodeType == XML_TEXT_NODE) {
                    $args = [
                        'content'       => trim((string) $element),
                        'tag'           => null
                    ];
                }

                // Normal simpleXMLElement, don't treat as content
                else
                    $args = [
                        'content'       => trim((string) $element),
                        'tag'           => (string) $element->getName(),
                        'classes'       => $classes,
                        'attributes'    => $attributes,     // convert attributes to patternAttribute
                        'id'            => $id,
                        'prerender'     => false
                    ];
                break;

            case '\\' . XML::class:
                $args = [
                    'tag'           => (string) $element->getName(),
                    'content'       => trim((string) $element),
                    'attributes'    => $attributes,
                    'prerender'     => false
                ];
                break;
            case '\\' . Node::class:
            default:
                $args['content'] = trim((string) $element);
                #foreach attributes add to args array
                foreach ($attributes->nodes as &$attr) {
                    $args[$attr->name] = $attr->content;
                }
                break;
        }

        if (!empty($args['content']) && $this->hasToken($args['content']))
            $args['content'] = $this->stringableToToken($args['content'], true);

        $r = new $render_type(...$args);

        if (isset($imprintAttributes['bind'])) {
            $this->_bound[$r->_render_id] = $imprintAttributes['bind'];
        }

        return $r;
    }

    public function exportNodeConstructor($node, $tab = '')
    {
        $prepend = '';
        $type = get_class($node);

        // Replace Appproach\\Render\\ with Render\\ or *\\Render\\ with ProjectRender\\
        if (strpos($type, 'Approach\\Render\\') === 0) {
            $type = substr($type, strlen('Approach\\'));
        } elseif (strpos($type, Scope::$Active->project . '\\Render\\') === 0) {
            $type = 'ProjectRender\\' . substr($type, strlen(Scope::$Active->project . '\\'));
        }

        $statement = 'new ' . $type . '( ';

        // Get the parameters of the Render\Node descendent's constructor
        $reflection = new \ReflectionClass($node);
        $parameters = $reflection->getConstructor()->getParameters();

        $blocks = $this->exportParameterBlocks($node, $parameters, $reflection, $tab);

        /**
         * Each parameter may be assigned a value or a symbol
         * Symbols are only used if a parameter block was produced
         * 
         * If a parameter block was produced, $block[$param]['symbol'] will equal
         * the name of the symbol to use for the parameter, 
         * 
         * $block[$param]['content'] will be a code block instantiating the symbol to prepend
         * Otherwise either use $node->$param or skip if it is not set
         */
        foreach ($parameters as $parameter) {
            $assignment = '';
            $param = $parameter->getName();

            if (!empty($blocks[$param]['symbol'])) {
                if ($blocks[$param]['isNode']) {
                    $assignment = $param . ': $' . $blocks[$param]['symbol'];
                    $prepend .= $tab . '// Instantiating ' . $blocks[$param]['symbol'] . ' for upcoming ' . $param . ' assignment' . PHP_EOL;
                    $prepend .= $blocks[$param]['content'] . PHP_EOL . PHP_EOL;
                } else $assignment = $param . ' : ' . $blocks[$param]['content'];
                $statement .= $assignment . ', ';
            }
        }
        $statement = trim($statement, ', ') . ' )';

        return [
            'prepend' => $prepend,
            'statement' => $statement
        ];
    }

    /**
     * Determines whether a given string contains a token.
     *
     * @param string $s The string to search for a token.
     * @param string $start The starting string of a token.
     * @param string $end The ending string of a token.
     * @return bool True if the string contains a token, false otherwise.
     */
    public function hasToken(string $s, string $start = self::TOKEN_SYMBOL_START, string $end = self::TOKEN_SYMBOL_END): bool
    {
        // If the string is empty, it can't contain a token
        if (empty($s)) {
            return false;
        }

        // Check if the string contains the start of a token
        $startPos = strpos($s, $start);

        // If the start of a token was found, check if the end of the token is also present
        return ($startPos !== false || strpos($s, $end, $startPos) !== false);
    }

    /**
     * Gets the token from a given string.
     *
     * @param string $s The string to get the token from.
     * @param string $start The starting string of a token.
     * @param string $end The ending string of a token.
     * @return string|false The token if it exists, false otherwise.
     */
    public function getToken(string $s, string $start = self::TOKEN_SYMBOL_START, string $end = self::TOKEN_SYMBOL_END): string|false
    {
        if (!$this->hasToken($s, $start, $end)) {
            return false;
        }

        $startPos = strpos($s, $start);
        $endPos = strpos($s, $end, $startPos);

        return substr($s, $startPos, $endPos - $startPos + strlen($end));
    }


    public function exportParameterBlocks($node, $parameters = [], $reflection)
    {
        $block = [];
        $symbol = $this->exportNodeSymbol($node);

        /**
         *  $name is the name of the property
         *  $property is the ReflectionProperty object for the property
         *  $value is the value of the property
         */
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            // If the parameter is not set, skip it
            if (empty($node->{$name}) && $node->{$name} !== 0 && $node->{$name} !== '0') continue;

            // Get the value of the parameter
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $assigment = $node->{$name} ?? $property->getValue($node);

            $block[$name]['isNode'] = false;
            $block[$name]['symbol'] = $name;

            // If the parameter is a Node, export it to a symbol
            if ($assigment instanceof Node) {
                // Use this node's symbol + __ + parameter name as parameter symbol
                $block[$name]['symbol'] = $symbol . '__' . $name;

                if ($assigment instanceof Token) {
                    $block[$name]['content'] = $this->exportNode(
                        node: $assigment,
                        export_symbol: $block[$name]['symbol'] . ' = $this->token_nodes[\'' . $assigment->name . '\']'
                    );
                } else {
                    $block[$name]['content'] = $this->exportNode(
                        node: $assigment,
                        export_symbol: $block[$name]['symbol']
                    );
                }

                $block[$name]['isNode'] = true;

                continue;
            }

            // Decorate values if needed
            $block[$name]['content'] = $this->decorateValues($assigment);
        }

        return $block;
    }

    /**
     * decorateValues($value)
     * 
     * Decorates values for export
     * 
     * @param mixed $value
     * @return string
     */
    public function decorateValues($value): string
    {
        $result = '';
        if (is_array($value)) {
            $result = '[';

            // Check if array is associative
            if (array_keys($value) !== range(0, count($value) - 1)) {
                $result .= PHP_EOL;
                foreach ($value as $key => $val) {
                    $result .= $this->decorateValues($key) . ' => ' . $this->decorateValues($val) . ', ' . PHP_EOL;
                }
                $result = trim($result, ', ' . PHP_EOL) . PHP_EOL;
            } else {
                foreach ($value as $key => $val) {
                    $result .= $this->decorateValues($val) . ', ';
                }
                $result = trim($result, ', ') . ']';
            }
        } elseif (is_string($value)) {
            $result = '\'' . $value . '\'';
        } elseif (is_bool($value)) {
            $result = $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            $result = 'NULL';
        } elseif (is_numeric($value)) {
            $result = $value;
        } elseif ($value instanceof \DateTimeInterface) {
            $result = '\'' . $value->format('Y-m-d H:i:s') . '\'';
        } elseif ($value instanceof \Closure) {
            $result = 'function(){ return \'' . $value . '\'; }';
        } elseif (is_object($value)) {
            if (method_exists($value, '__toJSON')) {
                $result = '\'' . $value->__toJSON . '\'';
            } elseif (method_exists($value, '__toString')) {
                $result = '\'' . $value . '\'';
            } /*  TODO: Check for a Decode Service to cast the object */
        } else {
            $result = $value;
        }

        return $result;
    }

    /**
     * exportNode
     * Converts a tree of objects which share Render\Node as a common ancestor into
     * a tree of PHP code which can be executed to generate the same tree of objects.
     * 
     * Relies on:
     *  - exportNodeSymbol          : 
     *    Algorithm to elect a symbol for a node
     *    'string'
     * 
     *  - exportNodeConstructor     : exportNode()/$constructor
     *    Generate a constructor call based on a type's parameters and the instance's property values
     *    [ 'prepend' => $prepend, 'statement' => $statement ]
     * 
     *  - exportParameterBlocks()   : exportNodeConstructor()/blocks
     *    Produces dependency symbol definitions for a node's parameters
     *    [ 'symbol' => $symbol, 'content' => $content]
     * 
     * 
     * Must keep in mind: 
     * parent nodes occur in the context of both element nodes and parameter node composition
     * 
     * e.g. a node may be a child of an element, but parameters may also be nodes composed of other nodes
     * this allows for token placement mid-parameter, eg <html data-[@ attr @]='{ "value": "[@ content @]" }'>
     * 
     * This is important for retaining the preceeding and proceeding content while being able to reference
     * tokens within the content is important
     */
    public function exportNode(Node $node, $parent = null, $export_symbol = null)
    {
        // track depth of recursion, for tabbing
        static $export_depth = 0;
        $tab = str_repeat("\t", $export_depth);
        $export_depth++;

        $id = $this->getNodeID($node);
        $type = $this->getNodeType($node);

        // If a symbol has already been defined, we don't need to assign it again
        $predefined = isset($this->_bound[$id]);

        $symbol = $export_symbol ?? $this->exportNodeSymbol($node);

        $constructor = $this->exportNodeConstructor($node, $tab);

        $append = $parent === null ? '$' : '$' . $parent . '[] = $';

        $child_exports = '';
        foreach ($node->nodes as $child) {
            $child_exports .= PHP_EOL . $this->exportNode($child, $symbol);
        }
        if (!empty($child_exports)) $child_exports .= PHP_EOL;

        $export_depth--;

        // If token was already bound before this pass started, don't assign it again
        $statement =  ' = ' . $constructor['statement'];
        if ($type == 'Token' && $predefined) {
            $statement = '';
        }
        return
            // $container.                                          // Define $_root_node if $parent is null
            // $block_exports .                                     // Export parameter blocks
            $constructor['prepend']                                 // Define symbols for parameters
            .
            $tab . $append . $symbol . $statement .                 // $parent[] = $MySymbol = new Type( ... );
            ';' .
            $child_exports                                          // Export child nodes
        ;
    }

    /**
     * exportNodeSymbol
     * 
     * Algorithm to elect a symbol for a node
     * Note: Only element nodes are sent to exportNodeSymbol(), parameter and token nodes have their own exports
     * 
     * @param Node $node
     * @param string $render_type
     * @param string $parent
     * @return string
     */
    public function exportNodeSymbol(Node $node)
    {
        $type = $this->getNodeType($node);
        $id = $this->getNodeID($node);

        // Set $this->generation_count[$type] to 0 if not set
        if (!isset($this->generation_count[$type])) {
            $this->generation_count[$type] = 0;
        }

        if ($type === 'Token' && !isset($this->_bound[$id])) {
			// normally _bound is set during Imprint::Prepare() when it encounters a node with 
			// the Imprint:bind="symbol" attribute - We will use it to bind token symbols here
			$this->_bound[$id] = 'this->token_nodes[\'' . $node->name . '\']';
			$this->found_tokens[$node->name] = $id ;
			
            $this->resolved_symbols[$id] = $this->_bound[$id];
        }

        // If $_bound[$node->_render_id] is set, assign it to $resolved_symbols[$node->_render_id]
        // If the symbol was previously resolved, do not increment $this->generation_count[$type]
        // This allows the pattern file to pass a symbol to a node through the Imprint:bind="symbol" attribute
        if (isset($this->_bound[$id])) {
            // Only count each node once per pattern
            if (!isset($this->resolved_symbols[$id])) {
                $this->generation_count[$type]++;
            }
            $this->resolved_symbols[$id] = $this->_bound[$id];
        }

        // If $resolved_symbols[$id] is still not set
        // Assign $resolved_symbols[$id] to [type]_[generation_count[type]]
        // Increment $this->generation_count[$type]
        if (!isset($this->resolved_symbols[$id])) {
            $this->resolved_symbols[$id] = $type . '_' . $this->generation_count[$type];
            $this->generation_count[$type]++;
        }

        return $this->resolved_symbols[$id];
    }


    /**
     * getNodeID
     * 
     * Returns a unique identifier for a given node.
     * 
     * @param Node $node The node to get the identifier for.
     * @return int|string The identifier for the node.
     */
    public function getNodeID(Node $node): int|string
    {
        $id = $node->_render_id;

        // If $type is a Token, create a unique ID by prepending 't-' to the token's name
        // This is to prevent collisions with other symbols
        $type = $this->getNodeType($node);
        if ($type === 'Token') $id = 't-' . $node->name;

        return $id;
    }


    /**
     * getNodeType
     * 
     * Returns the type of a given node as a string.
     * 
     * @param Node $node The node to get the type of.
     * @return string The type of the node.
     */
    public function getNodeType(Node $node): string
    {
        $type = get_class($node);
        // Remove the first two namespace paths from the type ( e.g. Approach\[layer] or [MyProject]\[layer] )
        $type = substr($type, strpos($type, '\\', strpos($type, '\\') + 1) + 1);
        return $type;
    }


    public function print($pattern = null)
    {
        $tree = $this->pattern[$pattern];
        $lines = $this->exportNode($tree);

        $project_render_NS = "\\" . Scope::$Active->project . '\\Render';


        $NS = $this->getImprintNamespace();
		$root = $this->exportNodeSymbol($tree);

		$token_list = 
			implode(									// Join the strings together
				',' , 									// with a comma,
				array_map( function ($token) {			// and alter each string,
						return '\'' . $token . '\'';	// to be wrapped in single quotes,
					},
					array_keys(							// after fetching the names 
						$this->found_tokens				// of all tokens found.
					)
				)
			);
		

        $file = <<<ImprintFile
		<?php
		namespace {$NS};
		use {$project_render_NS} as ProjectRender;
		use \Approach\Render;
			
			/**
			* This class was generated by Approach\Imprint::Mint()
			* It can be used to create a new Render tree based on the original Pattern
			*/
			class {$pattern} extends Render\Node
			{
			public static array \$tokens = [
				{$token_list}
			];
			public array \$token_nodes = [];
			
			public function __construct(array \$tokens = [])
			{
				{$lines}

				foreach(\$tokens as \$key => \$value){
					\$this->token_nodes[\$key]->content = \$tokens[\$key];
				}
				\$this->nodes[] = \${$root};
			}
		}

		ImprintFile;
        return $file;
    }

    public function getImprintNamespace(): string
    {

        $parts = [];
        $parts[] = Scope::$Active->project;
        $parts[] = 'Imprint';

        // remove any file extension from the end of the string
        $path = $this->imprint;
        $extension = strrchr($path, '.');
        $path = substr($path, 0, -strlen($extension));

        $parts = array_merge($parts, explode(ds, $path));

		$ns = join('\\', $parts);

		// ensure the namespace is using \ instead of / since path separator varies

		return str_replace('/', '\\', $ns);
        return $ns;
    }

    public function getImprintFileDir(): string
    {
        $imprint_path = path::imprint->get();

        // remove file extension from the end of the imprint file string
        $path = $this->imprint;
        $extension = strrchr($path, '.');
        $path = substr($path, 0, -strlen($extension));

        return $imprint_path . $path;
    }

    public function Mint($pattern = null)
    {
        $status = nullstate::ambiguous;
        try {
            if ($pattern !== null) {
                $file = $this->print($pattern);
                $imprint_dir = $this->getImprintFileDir();
                $pattern_path = $imprint_dir . ds . $pattern . '.php';

                // Check if the directory exists, if not, create it
                if (!is_dir($imprint_dir))  mkdir(directory: $imprint_dir, recursive: true);

                // echo 'Writing ' . $pattern_path . '...'.PHP_EOL;// . PHP_EOL . $file . PHP_EOL . PHP_EOL;
                $bytes_written = file_put_contents($pattern_path, $file);

                if ($bytes_written === false) {
                    // Find out why the file could not be written
                    echo 'Could not write to ' . $pattern_path . PHP_EOL;

                    // Check if the directory exists
                    if (!is_dir($imprint_dir)) {
                        throw new \Exception('The directory ' . $imprint_dir . ' does not exist and could not be created.');
                    }

                    // Check if the directory is writable
                    if (!is_writable($imprint_dir)) {
                        throw new \Exception('The directory ' . $imprint_dir . ' is not writable.');
                    }

                    // Check if the file exists
                    if (file_exists($pattern_path)) {
                        throw new \Exception('The file ' . $pattern_path . ' already exists and could not be overwritten.');
                    }

                    // Check if the file is writable
                    if (!is_writable($pattern_path)) {
                        throw new \Exception('The file ' . $pattern_path . ' is not writable.');
                    }

                    // If we get here, we don't know why the file could not be written
                    throw new \Exception('The file ' . $pattern_path . ' could not be written for an unknown reason.');
                }
            } else foreach ($this->pattern as $p => $tree) {
                echo ' trying.. ' . $p . PHP_EOL;

                $this->Mint($p);
            }
            $status = nullstate::defined;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $status;
    }


    /**
     * Recursively creates a render node from a SimpleXMLElement and its children.
     *
     * @param simpleXMLElement $element The SimpleXMLElement to create a render node from.
     * @param string $render_type The class name of the render node to create.
     * @return Render\Node|Stream The created render node.
     */
    public function recurse(simpleXMLElement $element, string $render_type = Node::class): Render\Node | Stream
    {
        // Create a new node of the given render type
        $render_node = $this->createNodeFromSimpleXML($render_type, $element);

        // Cascade over all child nodes of the current element, diving deepr into the tree
        foreach ($element->children() as $child) {
            $render_node->nodes[] = $this->recurse($child, $render_type);
        }

        return $render_node;
    }

    /**
     * This function takes a string or object and returns a token if the string contains a token name
     * 
     * @param mixed $string The input string or object to check for a token 
     * @param bool $force Whether to force the input to be handled like it has a token
     * @return mixed Returns the original input if it is not a string or a stringable object and $force is not set, or a new node containing the token if the input contains a token name
     */
    function stringableToToken(mixed $string, $force = false): mixed
    {
        // Check if the input is already a stringable object or not a string, and return it if it is and $force is not set
        $isStringableObject = (is_object($string) && method_exists($string, '__toString')) || is_a($string, Node::class);
        $isNotString = !is_string($string);

        if (($isStringableObject || $isNotString) && !$force) {
            return $string;
        }

        // Convert the input to a string
        $original = $string;
        $string = (string) $string;

        // Find the start and end of the token name in the string
        $start = strpos($string, self::TOKEN_SYMBOL_START) + strlen(self::TOKEN_SYMBOL_START);
        $end = strpos($string, self::TOKEN_SYMBOL_END);

        // If the token name is not found, return the original input
        if ($end === false || $start === false) {
            return $original;
        }

        // Create a new token object with the name found in the string
        $token = new Token(
            name: trim(
                substr($string, $start, $end - $start)
            )
        );
        // Add the token to the tokens array
        $this->tokens[$token->name] = $token;

        // Create new nodes for the content before and after the token name in the string
        $preToken = new Node(
            content: substr($string, 0, $start - strlen(self::TOKEN_SYMBOL_START))
        );

        $postToken = new Node(
            content: substr($string,  $end + strlen(self::TOKEN_SYMBOL_END))
        );


        #if pretoken and posttoken are empty, return only the token
        if (empty($preToken->content) && empty($postToken->content)) {
            return $token;
        }

        // Create a new node to hold the preToken, token, and postToken nodes
        $node = new Render\Node();

        if (!empty($preToken->content)) {
            $node[] = $preToken;
        }

        $node[] = $token;

        if (!empty($postToken->content)) {
            $node[] = $postToken;
        }

        // Return the new node
        return $node;
    }

    /**
     * extracts attributes from a simpleXMLElement in to keyed array
     * 
     * @param \simpleXMLElement $element
     * @return array
     */
    public static function extractAttributes(\simpleXMLElement $element, $prefix = null, $isPrefix = false): array
    {
        $attrs = $element->attributes($prefix, $isPrefix);
        $objectVars = get_object_vars($attrs);
        $attributes = $objectVars['@attributes'] ?? [];

        return $attributes;
    }

    /**
     * Get the render type from an XML element
     * 
     * @param SimpleXMLElement $element The XML element to get the render type from
     * @param string $render_type The default render type to use if none is found
     * @return string The render type
     */
    public function getRenderTypeFromElement(SimpleXMLElement $element, string $render_type = Node::class): string
    {
        if ($element->getName() == 'Pattern') {
            $render_type = (string) $element->attributes()->type;
        }
        // e.g. <node render:type="ifTrue" ></node>
        elseif ($element->getName() === 'node') {
            $renderAttributes = self::extractAttributes($element, 'render', true);
            $render_type = $renderAttributes['type'] ?? $render_type;
        }


        if (!class_exists($render_type)) {
            // If this Render class is installed to Approach
            if (class_exists('\\Approach\\Render\\' . $render_type)) {
                $render_type = '\\Approach\\Render\\' . $render_type;
            }
            // If this Render class is installed to the project
            elseif (class_exists(Scope::$Active->project . '\\Render\\' . $render_type)) {
                $render_type = Scope::$Active->project . '\\Render\\' . $render_type;
            } else {
                Scope::$Active->LayerError(
                    'Imprint used unknown type ' . $render_type . ' in ' . $this->imprint_dir . $this->imprint,
                    new \Exception
                );
            }
        }
        return $render_type;
    }

    public function Prepare(string $imprint = null): bool
    {
        $this->imprint = $imprint ?? $this->imprint;
        $success = false;

        # Check if the imprint is set
        if (!$this->imprint) {
            throw new \Exception(message: 'Missing imprint');
        }

        # Check if the imprint file exists
        if (!file_exists($this->imprint_dir . $this->imprint)) {
            throw new \Exception(message: 'Imprint file not found: ' . $this->imprint_dir . $this->imprint);
        }

        try {
			echo 'Minting pattern from: ' . $this->imprint_dir . $this->imprint . '...' . PHP_EOL;
            // Load the XML file, use html_entity_decode to prevent SimpleXML from converting HTML entities to their actual characters
			
			$file = file_get_contents($this->imprint_dir . $this->imprint);
			$file = html_entity_decode($file);
			$tree = simplexml_load_string($file);
			
            // $tree = simplexml_load_file($this->imprint_dir . $this->imprint);
            $imprint = $tree->xpath('//Imprint:Pattern');

            foreach ($imprint as $pattern) {
                $this->preparePattern($pattern);
            }

            $success = true;
        } catch (\Exception $e) {
            // If an exception is thrown, add an error message to the Render\Node
            $exceptional_message = new Render\Node;
            Scope::$Active->LayerError($e->getMessage(), $e);
            $exceptional_message->content = Scope::$Active->ErrorRenderable;
            $this->nodes[] = $exceptional_message;

            if (Scope::GetRuntime() != \Approach\runtime::production) {
                $exceptional_message->content = '';
            }
        }

        return $success;
    }

    private function preparePattern(\SimpleXMLElement $pattern)
    {
        // Get the pattern's render type
        $render_type = $this->getRenderTypeFromElement(element: $pattern);

        // Get the name of the pattern
        $name = (string) $pattern->attributes()->name;

        // Create a new Render\Node for the pattern
        $this->pattern[$name] = new Render\Node;

        // Recurse through the pattern's children and add them to the Render\Node
        foreach ($pattern->children() as $child) {
            $this->pattern[$name]->nodes[] = $this->recurse($child, $render_type);
        }
    }
}
