<?php

namespace help;

class composition
{
    /**
     * Composition routing is the process of determining the composition to use for a given request.
     * 
     * Understanding Composition Routing is important for understanding how to use Approach and how to extend it.
     * 
     * TLDR:
     * 
     *  - Default Route() will look for a "handler" composition class in a depth-first search of the namespace.
     * 	- Default Route() will then look for a "compose.php" file in the final namespace.
     * 	- Default Route() will then look for a "compose.php" file in the parent namespace recursively.
     *  - Default Route() will finally return nullstate::undeclared if no compose.php file is found.
     *  - Extend Composition and override Route() to provide custom routing logic.
     *  - Extend Composition and override Compose() to provide custom composition logic.
     * 
     * For basic usage, you may use the default routing configuration provided by Composition.
     * For intermediate usage, you may extend the default routing configuration with your own "handler" class.
     * For advanced usage, you should understand Composition's connection to Component and Service layers.
     * 
     *  - Every Composition instance has a type id matching a Composition class such as
     * 	  Composition\Dynamic
     * 	  Composition\Product\Details
     * 	  Composition\Product\List
     * 	  Composition\Product\Cart
     * 	  etc...
     * 
     * 	- Every Composition class inherits the default Route() action from Composition
     *	  Composition::Route
     * 	  - Will treat \My\Parent\Type\Class as if \My\Parent\Type is the namespace of the composition type
     * 	  - A Composition class can override the default Route() action to provide custom routing logic
     * 	  - When Route finds a "handler" Composition in the namespace, it will call the handler's Route() action
     *	  - When Route does not find a "handler" Composition in the namespace, it will return nullstate::undeclared 
     *	  - When Route reaches the last segment in the path, it will look for compose.php file in the namespace
     * 
     * 	- The compose.php file will 
     *    - Include a layout
     * 	  - Add renderables to Composition::$Active->content
     * 	  - Add Components to Composition::$Active->content
     * 	  - Components add themselves to Composition::$Active->Components
     * 	  - Components build into renderables
     * 	  - Services may access Composition and Component instances/methods
     * 
     * 	- Routes are defined by a Composition's Route() function.
     * 	- Beginning with Approach's root composition, Route is recursively evaluated until a composition is found.
     * 
     * 
     * @param string $url 				The URL to route.
     * @return \Approach\Composition	An instance extending Composition and matching the provided URL
     * @return \Approach\nullstate		If the URL is undeclared, undefined, or undefined_type
     * @throws \Exception				Route() may throw an exception downstream Route() calls produce errors
     * 
     */

    const Route = null;

    const create = null;
    const specialize = null;
    const use = null;
    const purpose = null;
    const flow = null;
    const checklist = null;
    const test = null;

    const moreinfo = null;
    const install = null;
    const contribute = null;
    const publish = null;
    const chat = null;
    const repo = null;
}
