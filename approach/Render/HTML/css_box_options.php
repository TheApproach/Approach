<?php

namespace Approach\Render\HTML;

use Approach\Render;
use \Approach\Render\Attribute;

enum css_box_options: string
{
    case margin_top         =  'margin-top';
    case margin_left        =  'margin-left';
    case margin_right       =  'margin-right';
    case margin_bottom      =  'margin-bottom';
    case padding_top        =  'padding-top';
    case padding_left       =  'padding-left';
    case padding_right      =  'padding-right';
    case padding_bottom     =  'padding-bottom';

    function asContainerOption(): string
    {
        return '_' . str_replace('-', '_', (string)$this);
    }
    function listContainerOptions()
    {
        $r = new Render\Node();
        foreach (self::cases() as $label) {
            $r->nodes[] = new Attribute(
                name: 'data-' . $label,
                content: $label->asContainerOption()
            );
        }
        return $r;
    }
}