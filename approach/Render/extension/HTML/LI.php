<?php

namespace Approach\Render\extension\HTML;

use Approach\Render\HTML;
use Approach\Render\HTML\Properties;
use \Approach\Render\Markup\ElementProperties;
use \Approach\Render\NodeProperties;

class LI extends HTML
{                                                // Uses ElementProperties
    public static $tag = 'li';
    use ElementProperties, Properties, NodeProperties {
        ElementProperties::selfContained insteadof Properties;
        ElementProperties::SkipRenderCascade insteadof NodeProperties;
    }
}
