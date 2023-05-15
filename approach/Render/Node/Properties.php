<?php	// Path: approach\Render\Node\Properties.php

namespace Approach\Render\Node;

use Approach\nullstate;
use Approach\Render\Node;

/**
 * 	@package Approach
 * 	@subpackage Render
 * 	@version 2.0.-1 beta
 * 
 * 	@license Apache 2.0
 * 	@since	2023-02-04
 * 	@see	\Approach\Render\Node
 * 
 */

trait Properties
{
    public static int $_render_count = 0;			// Can this become optional?
    public int $_render_id = 0;						// Can this become optional?
    public bool $prerender;							// Can this become optional?

	/**
	 * The code defines several rendering methods and a copy method for a PHP class.
	 * 
	 * @param properties An array of properties to set on the object being created in the __set_state
	 * method.
	 * 
	 * @return The code snippet contains several methods that perform different actions and return
	 * different values.
	 */
	public static function __set_state($properties){
		$node = new static(...$properties);
		foreach($properties as $key => $value){
			$node->$key = $value;
		}
		return $node;
	}
    /**
	 * This function sets a render ID and increments the render count.
	 */
	public function set_render_id()
    {
        $this->_render_id = static::$_render_count;
        $this->prerender = false;
        static::$_render_count++;
    }

	/**
	 * This recursively searches for a node with a specific ID within a tree structure.
	 * 
	 * @param root This is a reference to the root node of a tree structure.
	 * @param _render_id _render_id is a parameter that represents the unique identifier of a node in a
	 * tree structure. The function is designed to search for a node with a specific _render_id and return
	 * it if found.
	 * 
	 * @return Node|nullstate either a Node object or nullstate::null (which is likely a null value).
	 */
	public static function GetById(&$root, $_render_id): Node|nullstate
	{
		if ($root->_render_id == $_render_id) return $root;

		foreach ($root->children as $child)
		{
			$result = self::GetById($child, $_render_id);

			if ($result instanceof self)
			{
				if ($result->_render_id == $_render_id) return $result;
			}
		}

		return nullstate::null;
	}

	/**
	 * The function copies data from one variable to another with the option to specify the level of depth.
	 * 
	 * @param into A reference to the object that the current object will be copied into.
	 * @param level The level parameter is an optional integer value that determines the depth of the copy.
	 * It has a default value of 255, which means a full copy will be made. A value of 1 means a shallow
	 * copy will be made.
	 * @return the current object instance ().
	 */
    public function copyInto(&$into, $level = 255)
    {
        switch ($level) {
            case 255:
                $this->full_copyInto($into);
                break;
                //TO DO: Allow gradient copying of $level x $ChildNestDepthGapSize from 0-254,
                //When full_depth > 255, default to gap of full_depth % 256?
            case 1:
                $this->shallow_copyInto($into);
                break;
            default:
                $this->full_copyInto($into);
                break;
        }
        return $this;
    }

    /**
	 * This function performs a full copy of an object and its children into another object.
	 * 
	 * @param into  is a reference to the object that the current object () will be copied
	 * into. The function full_copyInto() is used to create a full copy of the current object and all
	 * its child objects, and copy them into the  object.
	 */
	public function full_copyInto(&$into)
    {
        $this->shallow_copyInto($into);
        for ($i = 0, $L = count($this->children); $i < $L; ++$i)    //Cascade
            $this->children[$i]->copyInto($into->children[$i]);
    }

    /**
	 * This function creates a shallow copy of an object and sets a render ID.
	 * 
	 * @param into  is a reference to the variable that will receive the shallow copy of the
	 * object. The "&" symbol before the parameter name indicates that it is a reference parameter,
	 * meaning that any changes made to the parameter inside the function will also affect the original
	 * variable outside the function.
	 */
	public function shallow_copyInto(&$into)
    {
        $into = clone $this;
        $this->set_render_id();
    }
}
