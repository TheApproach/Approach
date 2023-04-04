<?php

namespace Approach\Render\Markup;

use \Approach\Render\Attribute;

use \Stringable;

trait Properties
{
    public bool $selfContained;
    public null|string|Stringable $tag;
    public null|array|Attribute $attributes;
    // public Render\Stream $nodes = [];
    public $before = '';
    public $prefix = '';
    // public $content from NodeProperties
    public $suffix = '';
    public $after = '';
}
