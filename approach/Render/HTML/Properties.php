<?php

namespace Approach\Render\HTML;

use \Approach\Render;
use \Approach\Render\Attribute;
use \Approach\Render\Markup;
use \Traversable;
use \Stringable;

trait Properties
{
    use Markup\Properties;

    public Render\Node $classes;
    public array $styles;
    public null|string|Stringable $id;
    public static $NoAutoAttributes = [
        'html',
        'head',
        'link',
        'script',
        'meta',
        'title'
    ];


    function BuildStyles()
    {
    }

    function RenderTextNode()
    {
        if ($this->selfContained) {
            return $this->before . $this->content . $this->after;
        }

        return $this->before . $this->prefix . $this->content . $this->suffix . $this->after;
    }

    function RenderHead(): Traversable
    {
        yield
            $this->before .
            '<' .
            $this->tag .
			(count($this->classes->nodes) > 0 ? ' class="' .$this->classes.'"' : '') .
            $this->attributes .
            $this->BuildStyles() .
            ($this->selfContained ?            //prefix and suffix don't really make sense on  self-contained elements
                ' />' :                        //                :before <span>prefix content suffix</span> :after
                '>' . $this->prefix            //                :before <input value="abc" />  :after
            );
    }


    function buildContent()
    {
        foreach ($this->children as $renderObject) {
            $this->content .= $renderObject->render();
        }
    }

    function applyContainerOptions($options)
    {
        if (!empty($options['_enable']))
            if ($options['_enable'] === 1 || $options['_enable'] === '1') {
                $this->attributes['style'] .= 'background-color: ' . $options['_bgcolor'] . '; ';
            }

        $this->addInlineCSS(css_box_options::cases(), $options);

        if (!empty($options['_large_bg']) || !empty($options['_video_bg'])) {

            $node_backdrop = new Render\HTML(
                tag: 'div',
                classes: ['tallFit', 'wideFit', 'sheer', 'containerBackdrop'],
                styles: ['overflow: hidden;']
            );

            array_unshift($this->children, $node_backdrop);

            if (!empty($options['_large_bg'])) {
                $node_backdrop->attributes['style'] = 'background: ' .
                    'url(\'' . \Approach\Scope::GetDeploy(\Approach\deploy::uploads) . $options['_large_bg'] . '\'); ';
            }

            if (!empty($options['_video_bg'])) {
                $node_backdrop->nodes[] =
                    $video_container = new Render\HTML(...[
                        'tag'            => 'video',
                        'classes'         => ['tallFit', 'wideFit', 'sheer'],
                        'attributes'    => Attribute::fromArray([
                            'autoplay'    => 'autoplay',
                            'loop'        => 'loop',
                            'muted'        => 'muted',
                        ])
                    ]);
                $video_container->nodes[] =
                    $video_source = new Render\HTML(
                        tag: 'source',
                        attributes: Attribute::fromArray([
                            'type'      => 'video/mp4',
                            'src'       => $options['_video_bg']
                        ])
                    );
            }

            if (
                !empty($options['_overlaytransparency']) &&
                ($options['_overlaytransparency'] + 0) < 10
            ) {
                $alpha = ($options['_overlaytransparency'] * 10);
                if (empty($node_backdrop->attributes['style']))
                    $node_backdrop->attributes['style'] = '';
                $node_backdrop->attributes['style'] .= 'opacity: 0.' . $alpha . '; -ms-filter: \'progid:DXImageTransform.Microsoft.Alpha(Opacity=' . $alpha . ')\';'
                    . 'filter: alpha(opacity=' . $alpha . '); -moz-opacity: 0.' . $alpha . '; -khtml-opacity: 0.' . $alpha . ';  ';
                //= 'background-color: '.$options['_bgcolor'] .'; ';
            }
        }
        return true;
    }


    /*
	* @param array $rules Rules to be applied
	$rules = [
		"_padding_top",
		"_padding_left",
	];
	* @param array $source Source with corresponding rules, values and units
	$source = [
		"_padding_top": "12",
		"_padding_top_units": "px",
		"_padding_left": "2",
		"_padding_left_units": "rem",
	]
	*/
    function addInlineCSS($rules = [], $source = [])
    {
        foreach ($rules as $rule) {
            if (!empty($source[$rule])) {
                // Remove leading _ from html input. No real effect on well-formed input
                $css_rule = trim($rule, '_');
                $css_rule = str_replace('_', '-', $css_rule);

                // padding-left: 40px;
                // $css_rule: $css_value$css_unit;
                $css_unit = empty($source[$rule . '_unit']) ? '' : $source[$rule . '_unit'];
                $css_value = $source[$rule];

                // Append style to the style attribute
                $this->attributes['style'] .= $css_rule . ': ' . $css_value . $css_unit . '; ';
            }
        }
    }
}
