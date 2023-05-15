<?php

namespace Approach\Imprint;

use \Approach\path;
use \Approach\Scope;
use \Approach\Render;
use \Approach\Render\Stream;
use \Approach\Render\Node;
use \Approach\Approach;
use Approach\nullstate;
use \Approach\Render\HTML;
use \Approach\Render\XML;
use \Approach\Render\Attribute;
use \Approach\Render\Token;


class Imprint extends Render\Node\Keyed
{
    public array $tokens                    = [];
    public array $slots                     = [];
    public string $imprint_base;
	protected array $_used_symbols			= [];
	protected array $_bound					= [];
    protected static array $type_constructors = [];
	protected $register_token=[];

	public static function RegisterType(string $type, string $class): void
	{
		self::$type_constructors[$type] = $class;
	}

	public static function __static_init()
	{
		self::RegisterType('html', HTML::class);
		self::RegisterType('xml', XML::class);
		self::RegisterType('attribute', Attribute::class);
		self::RegisterType('token', Token::class);
	}

	public function __construct(
		public ?Node $pattern               = null,
		public ?string $imprint             = null,
		?string $imprint_base               = null,
	) {
		$this->pattern         = $pattern      ?? new Node;
		$this->imprint_base = $imprint_base    ?? Scope::$Active->GetPath(path::imprint);
	}

	public static function __set_state(array $properties): Imprint
	{
		$imprint = null;
		if(isset($properties['&that']))
			$imprint = &$properties['&that'];
		else
			$imprint = new Imprint();

		foreach ($properties as $key => $value) {
			if(property_exists($imprint, $key))
				$imprint->$key = $value;
			else switch ($key)
			{
				case 'pattern':
					$imprint->pattern = Node::__set_state($value);
					break;
				case 'slots':
					$imprint->slots = [];
					foreach ($value as $slot) {
						$imprint->slots[$slot->name] = $slot;
					}
					break;
				case 'tokens':
					$imprint->tokens = [];
					foreach ($value as $token) {
						$imprint->tokens[$token->name] = $token;
					}
					break;
			}
		}
		
		return $imprint;
	}

	public function __get(mixed $name): mixed
	{
		if (isset($this->slots[$name]))
			return $this->slots[$name]->content;
		else if (isset($this->tokens[$name]))
			return $this->tokens[$name]->content;
		else if (isset($this->pattern->$name))
			return $this->pattern->$name;
		else
			return null;
	}

	public function __set(mixed $name, mixed $value): void
	{
		self::__set_state(['&that' => $this]);

		if (isset($this->slots[$name]))
			$this->slots[$name]->content = $value;
		else if (isset($this->tokens[$name]))
			$this->tokens[$name]->content = $value;
		else if (isset($this->pattern->$name))
			$this->pattern->$name = $value;
	}

	public function __isset(string $name): bool
	{
		return isset($this->slots[$name]) || isset($this->tokens[$name]) || isset($this->pattern->$name);
	}

	public function __unset(string $name): void
	{
		if (isset($this->slots[$name]))
			unset($this->slots[$name]);
		else if (isset($this->tokens[$name]))
			unset($this->tokens[$name]);
		else if (isset($this->pattern->$name))
			unset($this->pattern->$name);
	}

	public function __debugInfo(): array
	{
		return [
			'pattern' => $this->pattern,
			'slots' => $this->slots,
			'tokens' => $this->tokens,
		];
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
     * Tokenize the imprint's slots with the given token dictionary
     * 
     * @param array $token_dictionary The token dictionary
     * @return void No return value
     * 
     */
    public function Tokenize(array $token_dictionary)
    {
        foreach ($token_dictionary as $key => $value) {
            $this->tokens[$key] = $value;

            if (isset($this->slots[$key]))
                $this->slots[$key]->content = &$this->token[$key];
        }
    }

    public static function arrayToCode(array $array): string
    {
        $return = '[ ';
        $encapsulate = '';
        if (empty($array)) {
            return '[]';
        }
        foreach ($array as $key => $value) {
            $encapsulate = '';

            if (is_string($value))
                $encapsulate = '\'';
            else if (is_array($value))
                $value = self::arrayToCode($value);

            if (is_int($key)) {
                $return .= $encapsulate . $value . $encapsulate . ',';
            } else {
                $return .= $key . ' => ' . $encapsulate . $value . $encapsulate . ',';
            }
        }

		$return = rtrim($return, ',');
        return $return . ' ]';
    }

    /**
		NOTICE: This is a temporary solution to the problem of creating a new instance of an \Approach\Render\ class from a string.
	
		new ReflectionMethod( $render_type, '__construct' )->invokeArgs( $r, $args );
		should probably be used as a better temp fix
		
		This function, Imprint::makeNodeFromSimpleXML(), will still need to map standard property names to the correct trait property names
		- it's fine to have this here, as it ultimately is a function of the Imprint concept
		- Imprints are stored in XML format, so XML and HTML are the only two render types that need to support special cases
		- For other render types the attributes are simply interpreted as a dictionary of constructor arguments
		- This way, if a user implements a complex render type, they can simply extend the Imprint class 
			- overload this function to handle their custom render type
			- calling the parent function to get the default behavior for built-in render types
			- usually wont be necessary, as the default behavior is to simply pass the attributes as constructor arguments

     */

	protected function makeNodeFromSimpleXML(string $render_type, \simpleXMLElement $element): \Approach\Render\Stream | \Approach\Render\Node
	{
		$args = [];
		$classes = [];
		$id = null;

		// handle attributes
		$attributesArray = self::extractAttributes($element);
		$imprintAttributes = self::extractAttributes($element, 'Imprint', true);


		foreach ($attributesArray as $key => $value)
		{
			// Move class and id attributes to their respective properties so they aren't duplicated as XML attributes
			if ($key === 'id' && is_a($render_type, XML::class, true))
			{
				$id = $value;
				unset($attributesArray[$key]);
			}

			if ($key === 'class' && is_a($render_type, HTML::class, true))
			{
				$classes = [...$classes, $value];
				unset($attributesArray[$key]);
			}
		}

		$classes = Render\Attribute::fromArray($classes);
		$attributes = Render\Attribute::fromArray($attributesArray);
		// end handle attributes

		switch ($render_type)
		{
			case '\\' . \Approach\Render\HTML::class:
				// Text node simpleXMLElement, treat as content
				if ($element->nodeType == XML_TEXT_NODE)
				{
					$args = [
						'content'       => (string) $element,
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

			case '\\' . \Approach\Render\XML::class:
				$args = [
					'tag'           => (string) $element->getName(),
					'content'       => (string) $element,
					'attributes'    => $attributes,
					'prerender'     => false
				];
				break;

			case '\\' . \Approach\Render\Node::class:
			default:
				$args['content'] = (string) $element;
				break;
		}

		$r = new $render_type(...$args);

		if( isset( $imprintAttributes['bind']) )
		{
			// echo 'binding..'. $imprintAttributes['bind'] .':'.$r->_render_id. PHP_EOL;
			$this->_bound[ $r->_render_id ] = $imprintAttributes['bind'];
		}

		return $r;
	}

    public function exportTree(Node $node, string $render_type, string $parent = null, $child_index = null, $recursive = true)
    {
        static $newline = PHP_EOL . "\t";
        static $tab = "\t";
		$skip = false;
		$sub_opt_flagged = true;
		$direct_value = false;
        $newline .= $tab;

		if(!$parent){
			$class_t = get_class($node);
			$short = explode('\\', $class_t);
			$short = array_pop($short);
			$base = isset($node->tag) ? $base = $node->tag : $short;

			if( !isset($this->_used_symbols[$base]) || !$this->_used_symbols[$base] ){
				$parent = $base;
			}
			elseif( isset($this->_used_symbols[$base]) && $this->_used_symbols[$base] ){
				$parent .= '_' . $node->_render_id;
			
				if( isset($this->_used_symbols[$base]) && $this->_used_symbols[$base] ){
					$parent =  'node' .$node->_render_id.'_'. $child_index;
				}
			}
		}

		// Get the name of the variable to use for this node
		$nodeName = $child_index === null ? 
			$parent : $parent . '_' . $child_index;

		// echo 'Checking for binding: '.$node->_render_id.PHP_EOL;
		if( isset( $this->_bound[ $node->_render_id ] ) && !is_a($node, Token::class, true) ){
			$nodeName = $this->_bound[ $node->_render_id ];
			$this->_used_symbols[$nodeName]=true;

		}

        // echo PHP_EOL . 'Exporting ' . $render_type . ' to PHP code.' . PHP_EOL;

        // Get the constructor parameters
        $reflection = new \ReflectionClass($render_type);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        // Get the values of the properties used in the constructor
        $arguments = [];
        $pre_construct = [];
		$pre_sub_construct = [];
		$skip = is_a($node, Attribute::class, true);

        // Loop over the constructor parameters of the render type
        // if(!$skip)
		foreach ($parameters as $parameter) {

            /**
             * 	$pre_construct is an array of code that will be executed before the constructor is called
             *  $name is the name of the property
             *  $property is the ReflectionProperty object for the property
             *  $type is the type of the property
             *  $value is the value of the property
             * 
             */
            $name = $parameter->getName();
            $property = $reflection->getProperty($name);
            $type = ($property->getType() ?? $parameter->getType()) . '';
            $property->setAccessible(true);
            $value = $node->$name; //$property->getValue($node);

			// echo PHP_EOL . 'Forwarding class property: ' . PHP_EOL .
			// $name . ': ' . var_export($value, true) . ' to  the ' . $render_type . ' constructor.' . PHP_EOL;



			if( is_string($value) )
			{
				if (strpos($name, '[@') !== false || strpos($value, '@]') !== false)
				{
					$tmp = $this->stringableToToken($value, true );

					// chick if tmp contains a token in ->nodes if so, add it to the pre_construct array
					foreach($tmp->nodes as $n)
					{
						if( is_a($n, Token::class, true) )
						{
							$pre_construct[$n->name] = [
								PHP_EOL, 
								...$this->exportTree(
									node: $tmp,
									render_type: get_class($tmp),
									parent: $n->name.'_token',
									child_index: null,
									recursive: false
								),
								'$this->token_nodes[\''.$n->name.'\'] = $'.$n->name.'_token;'
							];

							$value = '$'.$n->name.'_token';
							// $this->register_token[$n->name] = ' $' . $n->name . '_token;';

							$direct_value = true;
						}
					}
				}	
			}
            if ( is_a($value, Node::class, true) && !is_a($value, Token::class, true))
			{
                $class_t = get_class($value);
                $short = explode('\\', $class_t);
                $short = array_pop($short) . $node->_render_id;
                // Get the string that would produce the needed paramater as a variable
				
				if(!is_a($value, Render\Node\Keyed::class, true))
				{
					echo PHP_EOL . 'Diving into ' . $class_t.':'.$name . PHP_EOL;

					$pre_construct[$name] = $this->exportTree(
						node: $value,
						render_type: get_class($value),
						parent: $short . '__' . $name,
						child_index: null,
						recursive: false
					);
					$value = ' $opt[\'' . $name . '\'] ';
					$direct_value = true;
				}
				else
				{
					$garets_tear = $this->tokenizeAttributeType($value, $name);
					$value='';
					if($garets_tear){
						$pre_construct[$name] = $garets_tear;
						$sub_opt_flagged=true;
						$value = ' $opt[\'' . $name . '\'] ';
						$direct_value = true;
					}
				}
            }

            if (!empty($value) || $value === 0 || $value === '0') {
                if (is_array($value))
                    $arguments[$name] = self::arrayToCode($value);
                else {
                    $arguments[$name] = $direct_value ? $value : var_export($value, true);
                }
				$direct_value = false;
            }
        }
		$value = is_a($node, Token::class, true) ? '' : $value;

        $pre_constructor = '';
        $constructor = '';
        foreach ($pre_construct as $name => $code) {
            if ($pre_constructor === '')
                $pre_constructor .= PHP_EOL.$tab.$tab.$tab.'$opt = [];';

            $pre_constructor .=
                ($sub_opt_flagged ? '' : $newline .$tab. '$opt[\'' . $name . '\'] = ' ).
				implode($newline, $code)
			;

            $value = $tab.'$opt[\'' . $name . '\']';
        }
		// foreach ($pre_sub_construct as $line) {
		// 	$pre_constructor .= $newline . $line;
		// }

        $construct_type = '\\' . $render_type;
        // Detect if the $render_type begins with Approach\Render\
        // If it does, then it's a built-in render type
        if (strpos($render_type, 'Approach\\Render\\') === 0) {
            //Remove "Approach\" from the $render_type only if it is a built-in render type
            $cutoff = strlen('Approach\\');
            $construct_type = substr($render_type, $cutoff);
        }
        // Detect if the $render_type begins with Scope::$project \Render\
        // If it does, then it's a custom render type
        elseif (strpos($render_type, Scope::$Active->project . '\\Render\\') === 0) {
            //Remove "Approach\" from the $render_type only if it is a built-in render type
            $cutoff = strlen(Scope::$Active->project . '\\Render\\');
            $construct_type = 'ProjectRender\\' . substr($render_type, $cutoff);
        }

        // Create a string representation of the node's constructor with named arguments
        $constructor .= ' new ' . $construct_type . '(' .
            implode(
                ', ',
                array_map(
					function ($_name, $_value) {
						// if $name matches an attribute of a <Render:tag /> node, then it should be used in the constructor as a named argument			
						// example: <Render:BannerImprint slide_source="/__api/banner/9" />
						
                        return $_name . ' : ' . $_value;        // new $render_type( name: value, name: value, name: value,... );
                    },
                    array_keys($arguments),
                    $arguments
                )
            ) .
            ');';

        // Recursively export the child nodes
        $children = [];
        $index = 0;
        foreach ($node->nodes as $child) {
            $child_class = get_class($child);
            $children = array_merge($children, $this->exportTree($child, $child_class, $nodeName, $index, $recursive));
            $index++;
        }
		
		$newline = substr($newline, 0, -1);
        // Assign the node to a variable
        $full = '$' . $nodeName . ' = ' . $constructor;

		// if($nodeName === '$this' ){
		// 	$full = '';
		// }

		// If the node is a child of another node, then add it to the parent node's array
        if ($child_index !== null) {
            $full =
                $pre_constructor .
                $newline . '$' .  $parent  . '[] ='.
				$newline . '  $' . $nodeName . ' = ' . $constructor;
        }

        // Remove the last tab from $newline so that indention matches recursion level

        return array_merge([$full], $children);
    }
    
    /**
     * 
     * @param Attribute $attr
     * @param string $varname
     * @return array
     */
	public function tokenizeAttributeType(Attribute $attr, $property='fake_property'): array
	{
		// TODO: Check for NullNode instead, not sure if name/content are set to NULL properly, yet
		if( $attr->name === NULL && $attr->content === NULL && count( $attr->nodes ) == 0 ) 	return [];

		if(count($attr->nodes) == 0 && ($attr->name !== NULL || $attr->content !== NULL)){
			$all = [$attr];
		}
		else{
			$all = $attr->nodes;
		}

		$code = [];
		$append = ($property == 'attributes' || $property == 'classes') ? '' : '[]';

		$code[] = <<<attribute
			\$opt['{$property}'] = [];
			\$opt['{$property}']{$append} = [];
		attribute;

		foreach($all as $attr){
			$name = trim((string)$attr->name);
			$content = trim((string)$attr->content);

			if (strpos($name, '[@') !== false || strpos($name, '@]') !== false )
				$name = $this->stringableToToken($name, true, true);
			else 
				$name = '\''.$name.'\'';

			if( strpos($content, '[@') !== false || strpos($content, '@]') !== false)
				$content = $this->stringableToToken($content, true, true);
			else
				$content = '\''.$content.'\'';

			$name = empty($name)  ? 'NULL' : $name; 
			$content = empty($content)  ? 'NULL' : $content;
			// var_dump($attr);
			
			$string = <<<attribute

				\t\t\$sub_opt = [];
				\t\t\$sub_opt['name'] = {$name};
				\t\t\$sub_opt['content'] = {$content};
				\t\t\$opt['{$property}']{$append} = new Render\Attribute(\$sub_opt['name'], \$sub_opt['content']);
			attribute;
			$code[] = $string;
		}
		return $code;
	}

    public function print($pattern = null)
    {
        $tree = $this->pattern[$pattern];

		// $this->_bound[ $tree->_render_id] = '$this';
        $dom = $this->exportTree($tree, 'Approach\\Render\\Node');
        $lines = '';
        foreach ($dom as $line) {
            $lines .= $line . PHP_EOL;
        }

        $project_render_NS = "\\" . Scope::$Active->project . '\\Render';


        $NS = $this->getImprintNamespace();

        $file = <<<ImprintFile
		<?php

		namespace {$NS};
		use {$project_render_NS} as ProjectRender;
		use \Approach\Render;

		/**
		* 	This class was generated by Approach's Imprint::print()
		*	It is a PHP representation of the Imprint tree
		*	It can be used to create a new Imprint tree from the original XML file
		*
		*/

		class {$pattern} extends Render\Node
		{
			public array \$tokens = [];
			public array \$token_nodes = [];

			public function __construct(array \$tokens = [])
			{
				{$lines}

				foreach(\$this->tokens as \$key => \$value)
				{
					if(isset(\$tokens[\$key]))
						\$this->tokens[\$key]->content = \$tokens[\$key];
				}
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

        $parts = array_merge($parts, explode('/', $path));

        return join('\\', $parts);
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
            // var_export($this->pattern);
            /*

			/Users/tom/Dev/Suitespace/Approach/tests/Unit/../../Approach/Imprint/test/test/display.php

			*/

            if ($pattern !== null) {
                $file = $this->print($pattern);
                $imprint_dir = $this->getImprintFileDir();
                $pattern_path = $imprint_dir . '/' . $pattern . '.php';
				$imprint_dir = $imprint_dir;

				// Check if the directory exists, if not, create it
				if (!is_dir($imprint_dir))
					mkdir(directory: $imprint_dir, recursive: true);

				echo 'Writing ' . $pattern_path . PHP_EOL;
                file_put_contents($pattern_path, $file);
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


    public function recurse(\simpleXMLElement $element, string $render_type = \Approach\Render\Node::class)
    {
        // Create a new node of the given render type
        $render_node = $this->makeNodeFromSimpleXML($render_type, $element);

        // find and convert tokens of $render_node here?
        // if ($render_node has tokens)
        //     $this->tokens[$token_name] = &$token_node;
        // on detect token

        // Cascade over all child nodes of the current element, diving deepr into the tree
        foreach ($element->children() as $child) {
            $render_node->nodes[] = $this->recurse($child, $render_type);
        }

        return $render_node;
    }

    function stringableToToken(mixed $string, $force = false, $codify = false): mixed
    {
		if(strpos($string, '[@ ') === false || strpos($string, ' @]') === false || $force)
			echo PHP_EOL.' Tokenizing: ' . $string ;

        $a = (is_object($string) && method_exists($string, '__toString')) || is_a($string, \Approach\Render\Node::class);
        $b = !is_string($string);

        if (($a || $b) && !$force) {
            return $string;
        }

        $original = $string;
        $string = (string) $string;

        $start = strpos($string, '[@')+2;
        $end = strpos($string, '@]');

        if ($end === false || $start === false)
            return $original;

        $pre_token = new Node(
            content: substr($string, 0, $start-2)
        );

		echo ' ... ';

        $token = new Token(
            name: trim(
                substr($string, $start, $end - $start)
            )
        );

        $post_token = new Node(
            content: substr($string,  $end+2)
        );

        $node = new Render\Node();

        if (!empty($pre_token->content))
            $node[] = $pre_token;

        $node[] = $token;

        if (!empty($post_token->content))
            $node[] = $post_token;

		// If codify is true, then we want to return the token tree as a string which can be evaluated
		if($codify){
			$node = implode( 
				PHP_EOL,
				[
					PHP_EOL,
					...$this->exportTree(
						$node, 
						get_class($node),
						$token->name.'_token'
					),
					'$this->token_nodes[\'' . $token->name . '\'] = $' . $token->name . '_token;',
				]
			);

			$this->register_token[$token->name] = ' $' . $token->name . '_token;';
		}


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


    public function checkImprint($element, $render_type = \Approach\Render\Node::class): string
    {

        if ($element->getName() == 'Pattern') {
            $render_type = (string) $element->attributes()->type;

            if (!class_exists($render_type)) {

                // If this Render class is installed to Approach
                if (class_exists('\\Approach\\Render\\' . $render_type)) {
                    $render_type = '\\Approach\\Render\\' . $render_type;
                }

                // If this Render class is installed to the project
                elseif (class_exists(Scope::$Active->project . '\\Render\\' . $render_type))
                    $render_type = Scope::$Active->project . '\\Render\\' . $render_type;

                else
                    Scope::$Active->LayerError(
                        'Imprint used unknown type ' . $render_type . ' in ' . $this->imprint_base . $this->imprint,
                        new \Exception
                    );
            }
        }
        return $render_type; //, $imprint_type];
    }


    public function Prepare(string $imprint = null): bool
    {
        $this->imprint = $imprint ?? $this->imprint;
        $success = false;

        if (!$this->imprint) {
            throw new \Exception(message: 'Missing imprint');
        }
        try {
            // echo PHP_EOL.'Loading xml file: '.$this->imprint_base . $this->imprint.' ... '.PHP_EOL;
            $tree = simplexml_load_file($this->imprint_base . $this->imprint);
            $imprint = $tree->xpath('//Imprint:Pattern');

            foreach ($imprint as $pattern) {
                $render_type = $this->checkImprint($pattern);
                $name = (string) $pattern->attributes()->name;

                // echo PHP_EOL . ' Adding a ' . $render_type . 'Pattern: ' . $name . ' to Imprint..' . PHP_EOL;
                $this->pattern[$name] = new Render\Node;
                foreach ($pattern->children() as $child) {
                    $this->pattern[$name]->nodes[] = $this->recurse($child, $render_type);
                }
            }
            $success = true;
        } catch (\Exception $e) {
            $exceptional_message = new Render\Node;
            Scope::$Active->LayerError($e->getMessage(), $e);
            $exceptional_message->content = Scope::$Active->ErrorRenderable;
            $this->nodes[] = $exceptional_message;

            if (Scope::GetRuntime() != \Approach\runtime::production) {
                $exceptional_message->content = '';
            }

            // echo PHP_EOL . $e->getMessage() . PHP_EOL;
        }


        return $success;
    }

    public function Form()
    {
    }
}
