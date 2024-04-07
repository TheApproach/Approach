<?php

namespace Approach\Render;

use \Approach\Render\Node;
use \Approach\Render\XML;
use \Approach\Render\Attribute;
use \Stringable;

/**
 * HTML Class Reference
 * The HTML class extends the XML class and uses the HTML\Properties trait. It represents an HTML element and is used to create and manipulate HTML content in the Approach Rendering System.
 * 
 * The HTML class has a number of traits that allow it to define and set various HTML-specific properties, 
 * such as the tag, id, classes, attributes, etc..
 * 
 * Properties
 * $tag (string|Stringable|null): The name of the HTML tag.
 * $id (string|Stringable|null): The id attribute of the element.
 * $classes (string|array|Attribute|null): The class attribute of the element. If a string or array is passed, it will be converted to an Attribute object.
 * $attributes (array|Attribute|null): Other attributes of the element. If an array is passed, it will be converted to an Attribute object.
 * $content (string|Stringable|Stream|self|null): The content of the element.
 * $styles (array): An array of inline style rules.
 * $prerender (bool): A flag indicating whether the element has been prerendered or not.
 * $selfContained (bool): A flag indicating whether the element is self-contained or not.
 * 
 * Methods
 * __construct(): The constructor for the HTML class.
 * 
 * @package Approach\Render
 * @version 1.0.0
 * @since 1.0.0
 * @see \Approach\Render\Node
 * @see \Approach\Render\XML
 * @see \Approach\Render\HTML\Properties
 * @see \Approach\Render\HTML\Tag
 * @see \Approach\Render\HTML\ID
 * @see \Approach\Render\HTML\Classes
 * @see \Approach\Render\HTML\Attributes
 * @see \Approach\Render\HTML\Content
 * @see \Approach\Render\HTML\Styles
 * @see \Approach\Render\HTML\Prerender
 * @see \Approach\Render\HTML\SelfContained
 * 
 * @license Apache-2.0
 * @link https://approach.dev
 * 
 * @example
 * 
 * // Create a new HTML element
 * $element = new HTML('div');
 * 
 * // Set the id attribute
 * $element->id = 'my-id';
 * 
 * // Set the class attribute
 * $element->classes = ['my-class', 'my-other-class'];
 * 
 * // Set the content
 * $element->content = 'Hello, world!';
 * 
 * // Set the style attribute
 * $element->styles = [
 *   'color' => 'red',
 *   'background-color' => 'blue',
 * ];
 * 
 * // Render the element
 * echo $element->render();
 * 
 * // Output:
 * // <div id="my-id" class="my-class my-other-class" style="color: red; background-color: blue;">Hello, world!</div>
 * 
 */


class HTML extends XML
{
    use HTML\Properties;

    public function __construct(
        public null|string|Stringable $tag = NULL,
        public null|string|Stringable $id = null,
		null|string|array|Node|Attribute $classes = null,
        public null|array|Attribute $attributes = new Attribute,
        public null|string|Stringable|Stream|self $content = null,
        public array $styles = [],
        public bool $prerender = false,
        public bool $selfContained = false,
    ) {
		$this->classes = new Node;
        if (is_array($classes) || is_string($classes))
		{
			$classes = is_array($classes) ? $classes : explode(' ', $classes);

			foreach($classes as $class){
				$this->classes[] = new Node($class);
			}
		}
		elseif($classes instanceof Attribute)
			$this->classes[] = $classes;

        if (is_array($attributes))
            $this->attributes = Attribute::fromArray($attributes);
        $this->set_render_id();
    }
}
