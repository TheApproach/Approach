<?php

/*
	Title: PHP-to-JavaScript Event Binding and Handling + Script Placements for Renderables


	Copyright 2002-2014 Garet Claborn

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.

*/
namespace Approach\Event;
use Approach\Composition\Composition;
use \Approach\Render\HTML;

require_once('Render.php');

$RegisteredScripts = new HTML('script');
$RegisteredScripts->attributes['type']="text/javascript";


function GetChildScripts(&$root)
{
  $Container=Array();
  if(isset($root->children))
  foreach($root->children as $child)   //Get Script Type Renderables In Head
  {
      if($child->tag == 'script')
      {
          $Container[]=$child;
      }
  }
  return $Container;
}

function RegisterScript($inboundScript, $toHead=true, $comment='')
{
    if($inboundScript=='') return false;
    if($comment != ''){ $comment = "\r\n/* $comment ".var_dump(debug_backtrace(),true)."*/\r\n"; }
    global $RegisteredScripts;

    $Head=new HTML('head');
    $RegisteringScript=new HTML;
    $HeadScripts=[];

    if($toHead)  //Aggregate all dynamic scripts to last script renderable in head
    {
		$x = new Composition();
        $Head=&\Approach\Utility\GetRenderablesByTag(
			Composition::$Active->DOM,
			'head'
		)[0];
        $HeadScripts=&GetChildScripts($Head);
        $NumScriptsInHead= count( $HeadScripts );

        if($NumScriptsInHead > 0 && isset($HeadScripts[$NumScriptsInHead]))
        {
            $RegisteringScript = $HeadScripts[$NumScriptsInHead];
        }
        else
        {
          $RegisteringScript = new HTML('script');
          $RegisteringScript->attributes['type']="text/javascript";
          $Head->nodes[]=$RegisteringScript;
        }

        $RegisteringScript->content .= $comment;

        if( is_array($inboundScript) )
        {
            foreach($inboundScript as $scriptLines)
            {
                $RegisteringScript->content .= '' . $scriptLines;       //Append Lines of Script
            }
        }
        elseif( is_string($inboundScript) )
        {
            $RegisteringScript->content .= $inboundScript;       //Append JavaScript Code Directly
        }
    }
    elseif(!$toHead)                                            //Migrate to end of the body
    {
        $RegisteredScripts->content .= $comment;

        if( is_array($inboundScript) )
        {
            foreach($inboundScript as $scriptLines)
            {
                $RegisteredScripts->content .= '' . $scriptLines;       //Append Lines of Script
            }
        }
        elseif( is_string($inboundScript) )
        {
            $RegisteredScripts->content .= $inboundScript;       //Append JavaScript Code Directly
        }

    }
}





$APPROACH_JQUERY_EVENTHANDLING= <<<APPROACH_HEREDOC_SYNTAX


(function( $ ) {
  $.fn.ApproachEventHandling = function(event) {

    var target = event.target;
    var bubbleClasslist = this.get(0).className.split(/\s+/);
    var targetClasslist = target.className.split(/\s+/);
    var i, L=0;

    /*******
    Check for registered events on click, mouseenter, mouseleave for the
    element which the event originated from and it's bubble
    *******/


    /********************************/
    /*  Check all bubble classes    */

    for(i=0, L=bubbleClasslist.length; i<L; i++){
      if(event.type == 'click')
      {
        switch(bubbleClasslist[i])
        {
            {{{{{{{BUBBLE_CLASSES_CLICK}}}}}}}
            default: break;
        }
      }
      if(event.type == 'mouseenter')
      {
        switch(bubbleClasslist[i])
        {
            {{{{{{{BUBBLE_CLASSES_MOUSEENTER}}}}}}}
            default: break;
        }
      }
      if(event.type == 'mouseleave')
      {
        switch(bubbleClasslist[i])
        {
            {{{{{{{BUBBLE_CLASSES_MOUSELEAVE}}}}}}}
            default: break;
        }
      }
    }

    /*  Check Bubble ID    */
    if(event.type == 'click')
    {
        switch(this.get(0).id)
        {
            {{{{{{{BUBBLE_ID_CLICK}}}}}}}
            default: break;
        }
    }
    if(event.type == 'mouseenter')
    {
        switch(this.get(0).id)
        {
            {{{{{{{BUBBLE_ID_MOUSEENTER}}}}}}}
            default: break;
        }
    }
    if(event.type == 'mouseleave')
    {
      switch(this.get(0).id)
      {
          {{{{{{{BUBBLE_ID_MOUSELEAVE}}}}}}}
          default: break;
      }
    }

    /*******END BUBBLE SECTION*******/
    /********************************/
    /******START TARGET SECTION******/

    /********************************/
    /*  Check all target classes    */
    for(i=0, L=targetClasslist.length; i<L; i++)
    {
      if(event.type == 'click')
      {
        switch(targetClasslist[i])
        {
            {{{{{{{TARGET_CLASSES_CLICK}}}}}}}
            default: break;
        }
      }
      if(event.type == 'mouseenter')
      {
        switch(targetClasslist[i])
        {
            {{{{{{{TARGET_CLASSES_MOUSEENTER}}}}}}}
            default: break;
        }
      }
      if(event.type == 'mouseleave')
      {
        switch(targetClasslist[i])
        {
            {{{{{{{TARGET_CLASSES_MOUSELEAVE}}}}}}}
            default: break;
        }
      }
    }


    /*  Check target ID    */
    if(event.type == 'click')
    {
        switch(target.id)
        {
            {{{{{{{TARGET_ID_CLICK}}}}}}}
            default: break;
        }
    }
    if(event.type == 'mouseenter')
    {
        switch(target.id)
        {
            {{{{{{{TARGET_ID_MOUSEENTER}}}}}}}
            default: break;
        }
    }
    if(event.type == 'mouseleave')
    {
      switch(target.id)
      {
          {{{{{{{TARGET_ID_MOUSELEAVE}}}}}}}
          default: break;
      }
    }
};

})( jQuery );

APPROACH_HEREDOC_SYNTAX;

function RegisterJQueryEvent($type, $casing, $script)
{
    global $APPROACH_JQUERY_EVENTHANDLING;
    global $APPROACH_JQUERY_EVENTS;

    $code='';
    foreach((array)$script as $lines){  $code .= $lines; }  //convert array to string (php special)
    $code = 'case "'. $casing.'": '. $code . PHP_EOL;

    switch($type)
    {
        case 'BUBBLE_CLASS_CLICK':    $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{BUBBLE_CLASSES_CLICK}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_CLASSES_CLICK}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'BUBBLE_CLASS_MOUSEENTER':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                          '{{{{{{{BUBBLE_CLASSES_MOUSEENTER}}}}}}}',
                                          $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_CLASSES_MOUSEENTER}}}}}}}',
                                          $APPROACH_JQUERY_EVENTHANDLING);
                                          break;
        case 'BUBBLE_CLASS_MOUSELEAVE': $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                          '{{{{{{{BUBBLE_CLASSES_MOUSELEAVE}}}}}}}',
                                          $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_CLASSES_MOUSELEAVE}}}}}}}',
                                          $APPROACH_JQUERY_EVENTHANDLING);
                                          break;
        case 'BUBBLE_ID_CLICK':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{BUBBLE_ID_CLICK}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_ID_CLICK}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'BUBBLE_ID_MOUSEENTER':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{BUBBLE_ID_MOUSEENTER}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_ID_MOUSEENTER}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'BUBBLE_ID_MOUSELEAVE':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{BUBBLE_ID_MOUSELEAVE}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{BUBBLE_ID_MOUSELEAVE}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_CLASS_CLICK':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_CLASSES_CLICK}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_CLASSES_CLICK}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_CLASS_MOUSEENTER':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_CLASSES_MOUSEENTER}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_CLASSES_MOUSEENTER}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_CLASS_MOUSELEAVE':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_CLASSES_MOUSELEAVE}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_CLASSES_MOUSELEAVE}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_ID_CLICK':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_ID_CLICK}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_ID_CLICK}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_ID_MOUSEENTER':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_ID_MOUSEENTER}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_ID_MOUSEENTER}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        case 'TARGET_ID_MOUSELEAVE':  $APPROACH_JQUERY_EVENTHANDLING=str_replace(
                                        '{{{{{{{TARGET_ID_MOUSELEAVE}}}}}}}',
                                        $code . 'break; '.PHP_EOL.'{{{{{{{TARGET_ID_MOUSELEAVE}}}}}}}',
                                        $APPROACH_JQUERY_EVENTHANDLING);
                                        break;
        default: break;

    }


    $binding = array();
    if( strpos($type, 'CLASS') != false)
    {
        $binding['qualifier'] = '.' . $casing;
    }
    elseif( strpos($type, 'ID') != false)
    {
        $binding['qualifier'] = '#' . $casing;
    }

    if( strpos($type, 'CLICK') != false)
    {
        $binding['trigger'] = 'click';
    }
    elseif( strpos($type, 'MOUSEENTER') != false)
    {
        $binding['trigger'] = 'mouseenter';
    }
    elseif( strpos($type, 'MOUSELEAVE') != false)
    {
        $binding['trigger'] = 'mouseleave';
    }

    $APPROACH_JQUERY_EVENTS[]=$binding;
}



function CommitJQueryEvents()
{
    global $APPROACH_JQUERY_EVENTHANDLING;
    global $APPROACH_JQUERY_EVENTS;

    $ApproachEventRegistration = '$(document).ready( function(){' . PHP_EOL;

    foreach($APPROACH_JQUERY_EVENTS as $binding)
    {
        $ApproachEventRegistration .= PHP_EOL . '$("' . $binding['qualifier'] . '").bind("' . $binding['trigger'] .'", function(event){ $(this).ApproachEventHandling(event);} );';
    }

    $ApproachEventRegistration .=  PHP_EOL .'}' . PHP_EOL . ');' .PHP_EOL . PHP_EOL . $APPROACH_JQUERY_EVENTHANDLING;

    $ApproachEventRegistration=preg_filter('~(\{){7}(.){5,}(\}){7}~','',$ApproachEventRegistration);
    RegisterScript($ApproachEventRegistration, true, "Event Registration From Approach");
}




?>
