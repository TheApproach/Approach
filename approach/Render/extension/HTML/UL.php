<?php

namespace Approach\Render\extension\HTML;

use \Approach\Render\HTML;
use Approach\Render\HTML\Properties;
use \Approach\Render\Markup\ElementProperties;
use \Approach\Render\NodeProperties;

class UL extends HTML
{                                                        // Uses ElementProperties
    public static $tag = 'ul';
    public array $items = [];

    use ElementProperties, Properties, NodeProperties {
        ElementProperties::selfContained insteadof Properties;
        ElementProperties::SkipRenderCascade insteadof NodeProperties;
    }

    public function AddItem($val, $key = null)
    {
        $item = new LI(content: $val);
        $this->nodes[] = $item;
        if ($key !== null) {
            $ptr = end($this->nodes);
            $this->items[$key] = &$ptr;
        }
    }
    public function GetItem($index = NULL, $key = NULL, $value = NULL)
    {
        if (NULL !== $this->items[$key]) {
            return $this->items[$key];
        }
        if (NULL !== $index && !empty($this->nodes[$index])) {
            return $this->nodes[$index];
        }
        if (NULL !== $value) {
            foreach ($this->nodes as $node) {
                if ($node->content == $value) {
                    return $node;
                }
            }
        } else {
            return NULL;
        }
    }
}
