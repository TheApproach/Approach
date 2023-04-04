<?php
namespace Approach\Render\Ncurses;

use \Approach\Render\Node;

/**
 * types enum - defines the types of nodes that can be rendered
 * 
 * @package		Approach
 * @subpackage	Render
 * @subpackage	Ncurses
 * @subpackage	Types * 
 * @version		2.0.0
 * @category	Renderable
 * @category	Layout
 * @category	Types
 * 
 */

enum types:int {
    case Window = 0;
    case Frame = 1;
    case Button = 2;
    case Label = 3;
    case Textbox = 4;
    case Checkbox = 5;
    case Radio = 6;
    case Listbox = 7;
    case Combobox = 8;
    case Menu = 9;
    case Menuitem = 10;
    case Scrollbar = 11;
    case Progressbar = 12;
    case Dialog = 13;
    case Form = 14;
    case Panel = 15;
    
    public function __toString():string {
        return $this->name;
    }

    public function make(array $init) : Node
	{
		$typename = 'Approach\Render\Ncurses\\' . $this->name;

		if (!is_subclass_of($typename, '\\Approach\\Render\\Node'))
		{
			if (!class_exists($typename))
				throw new \Exception('Class ' . $typename . ' does not exist');
			else
				throw new \Exception('Class ' . $typename . ' does not extend Render\\Node');
		}
		
		return new $typename(...$init);
	}
}





enum types: int
{
	case Window = 0;
	case Frame = 1;
	case Button = 2;
	case Label = 3;
	case Textbox = 4;
	case Checkbox = 5;
	case Radio = 6;
	case Listbox = 7;
	case Combobox = 8;
	case Menu = 9;
	case Menuitem = 10;
	case Scrollbar = 11;
	case Progressbar = 12;
	case Dialog = 13;
	case Form = 14;
	case Panel = 15;

	public function __toString(): string
	{
		return $this->name;
	}

	public function make(array $init): Node
	{
		$typename = 'Ncurses\\' . $this->name;

		if (!is_subclass_of($typename, 'Ncurse\\NcursesLayout'))
		{
			if (!class_exists($typename))
				throw new \Exception('Class ' . $typename . ' does not exist');
			else
				throw new \Exception('Class ' . $typename . ' is not a subclass of Ncurse\\NcursesLayout');
		}

		return new $typename(...$init);
	}
}