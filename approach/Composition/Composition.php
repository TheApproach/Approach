<?php

/*************************************************************************

APPROACH

 *************************************************************************/

namespace Approach\Composition;

use \Approach\Scope;
use \Approach\path;
use \Approach\Component\Component;
use \Approach\nullstate;
use \Approach\Render\Node;
use \Approach\Render\XML;
use \Approach\Render\HTML;
use \Exception;

class Composition
{
    public static $types = [];
    public static $type_index = [];
    public static $routes = [];
	public static $routed_namespace = '';

    const PUBLISH_FULL = 0;
    const PUBLISH_API = 1;
    const PUBLISH_JSON = 2;
    const PUBLISH_EMBED = 3;
    const PUBLISH_LAYOUT = 4;
    const PUBLISH_EDIT = 5;
    const PUBLISH_SILENT = 6;

    public static $Active;
    public static $CurrentTheme = 'Modern';
    public $DOM;
    public array $ComponentList=[];
    public $InterfaceMode = false;
    public $OwnerID;

    public array $Context;
    public $meta;
    public $intents;
    public $access_mode = 'full';

    public $editable = [];

    public static $marginOptions = [
        "_margin_top",
        "_margin_left",
        "_margin_right",
        "_margin_bottom",
    ];
    public static $paddingOptions = [
        "_padding_top",
        "_padding_left",
        "_padding_right",
        "_padding_bottom",
    ];

    function __construct(
        public array $options = [],
        public bool $activate = false,
        string $url = '.'
    ) {

        if ($activate) $this::$Active = &$this;

        // TODO: check if we can remove this?
        if (!empty($_GET['new_version'])) {
            $this->InterfaceMode = true;
        }
    }

    public static function toFile($filename, $data)
    {
        $fh = fopen($filename, 'w');
        if ($fh)
            fwrite($fh, $data);
        else $data = '<span class="ioERROR error"> Can\'t open ' . $filename . ' file. </span>';
        fclose($fh);
    }

    public static function resolveScope(string $target_namespace): array|nullstate
    {
		if (!key_exists($target_namespace, self::$types)) { //   '\\Approach\\Composition'
			return nullstate::undefined_type; 
		}

		$scopes = [''];

		// As we are recursing, we need to keep track of the current scope and the target scope
		// Get both as array broken down by \ (backslash) such as ['Approach', 'Composition', 'handler']
        $target = explode('\\', Scope::$Active->project .'\\Composition\\'. $target_namespace . '\\handler');
        $here = explode('\\', trim(static::class, '\\'));

        $scope_changed = false;

        for ($i = 0, $L = count($target); $i < $L; $i++) {
			// Merge the namespaced class name up until the point where the scope changes
			// e.g. ['Approach', 'Composition', 'handler'] => ['Approach\\Composition', 'handler']
            if (isset($here[$i]) && ($here[$i] === $target[$i]) && !$scope_changed) 
			{
                $scopes[0] .= '\\' . $target[$i];
            } 
			// Once $here and $target differ, add the rest of $target 
			// to be resolved in the next recursion
			else 
			{
                $scope_changed = true;
                $scopes[] = $target[$i];
            }
        }

        return $scopes;
    }

    public static function attachScope($url, $scopes = [])
    {
        $handler_class = '';
        $base_namespace = array_shift($scopes); // e.g. '\\Approach\\Composition'
        foreach ($scopes as $scope) {
            $base_namespace .= '\\' . $scope;
            $handler_class = $base_namespace . '\\handler';

            if (get_class() !== $handler_class && class_exists($handler_class)) {
                // echo '</br> return '. $handler_class . '::Route("' . $url . '"); </br>';
                return $handler_class::Route($url);
            }
        }

        // if (!class_exists($handler_class)) {
		// 	echo '<br/><br/>'.PHP_EOL.PHP_EOL.'Type: ( ' . $handler_class . ' ) not found for '.$url.PHP_EOL;
        //     // $e = new \Exception(
        //     //		'Type: ' . $handler_class . ' '.PHP_EOL.
        //     // 		'for URL: '. $url . ' is not defined.'.PHP_EOL,
        //     //		code: nullstate::undefined->value
        //     // );
        //     // throw ($e);
        //     return nullstate::undefined;
        // }
		// echo '<br/><br/>'.PHP_EOL.PHP_EOL.'Type: ( ' . $handler_class . ' ) found for '.$url.PHP_EOL;

        // When all scopes were resolved in previous recursions, instantiate this class
		self::$Active = new self(url: $url);

		Scope::$Active->type = $base_namespace;
		Scope::SetPath(
			path::route, 
			dirname(
				(new \ReflectionClass(static::class))->getFileName()) 
			);
        return self::$Active;
    }

    public static function GetTypeByName($typename): int|string|nullstate
    {
        if (!key_exists($typename, self::$types)) return nullstate::undefined;

        return self::$types[$typename];
    }

    public static function GetTypeByID($id): string|nullstate
    {
        return self::$type_index[$id] ?? nullstate::undefined_type;
    }
    public static function GetTypeByURL($url): string|nullstate
    {
        if (!key_exists($url, self::$routes)) return nullstate::undeclared;

        return self::GetTypeByID(
            self::$routes[$url]
        );
    }

    public static function Route(string $url, $silent = false): Composition|nullstate
    {
        $target_namespace = self::GetTypeByURL($url);

        if ($target_namespace instanceof nullstate) {
			// TODO: perform a resource load to check if the url is valid before returning
			// return if url is not cached
            return $target_namespace;
        }

        // $target_namespace = Scope::$Active->project . '\\' . $target_namespace;

        $scopes = self::resolveScope(target_namespace: $target_namespace);
        if ($scopes === nullstate::undefined_type) {
            // $e = new \Exception(
            //     'Type ' . $target_namesapce . ' is not defined.',
            //     code: nullstate::undefined_type->value
            // );
            // throw ($e);
            return nullstate::undefined_type;
        }

        return self::attachScope($url, $scopes);    // returns a Composition object
    }

    public function Layout($root = null, $LayoutStructure = [])
    {
        if ($root === null) {        // checks XML->tag
            $root = XML::GetFirstByTag($this->DOM, "body");
        }
        // elseif ($root instanceof XML) {	// checks HTML->id
        // 	$root = HTML::GetElementById($this->DOM, $root->id);
        // }
        elseif (is_int($root)) {    // checks Node->render_id
            $root = Node::GetById($this->DOM, $root);
        }

        $ParentURLNode = null;
        $CurrentURLNode = end($this->Context['traversed']);
        if (count($this->Context['traversed']) > 2) {
            $ParentURLNode = $this->Context['traversed'][count($this->Context['traversed']) - 2];
        } else {
            $ParentURLNode = $CurrentURLNode;
        }
        $this->OwnerID = $ParentURLNode['owner'];


        //LayoutStructure is json_decode of the layout json
        foreach ($LayoutStructure as &$outline) {
            $this->CrawlNodes($root, $outline['self'], $outline['children']);
            $outline = null;
        }
        $LayoutStructure = null;
    }

    public function CrawlNodes(&$root, $outline, $descendants)
    {
        global $SupportPath;
        global $RemoteBase;
        global $ApproachConfig;


        if (!isset($outline['options'])) $outline['options'] = [];

        // Only needed until all old sites updated to new compositor v3 pages
        if (empty($outline['col-schema']) && !empty($outline['options']['col-schema']))
            $outline['col-schema'] = $outline['options']['col-schema'];
        $outline['col-schema'] = explode(',', $outline['col-schema']);                     //convert comma-delimited to array
        $colIndex = 0;



        // Create layout row >> column >> component list structure, based on layout json
        $root->nodes[] =
            $node = new HTML(...['tag' => 'li', 'classes' => ['col-md-12', 'layoutRow', '.composed' . $outline['type']]]);
        $node->attributes['style'] = '';
        $node->attributes['data-colschema'] = implode(',', $outline['col-schema']);

        if ($this->InterfaceMode) {
            $node->attributes['data-container-settings'] =
                htmlspecialchars(
                    json_encode($outline['options']),
                    ENT_QUOTES
                );
        }


        if (!empty($outline['options']['_enable']))
            if ($outline['options']['_enable'] === 1 || $outline['options']['_enable'] === '1') {
                if (!empty($outline['options']['_bgcolor']))
                    $node->attributes['style'] .= 'background-color: ' . $outline['options']['_bgcolor'] . '; ';
            }

        $node->addInlineCSS(static::$paddingOptions, $outline['options']);
        $node->addInlineCSS(static::$marginOptions, $outline['options']);

        if (!empty($outline['options']['_large_bg']) || !empty($outline['options']['_video_bg'])) {

            $node->nodes[] =
                $node_backdrop = new HTML(
                    ...[
                        'tag' => 'div',
                        'classes' => ['tallFit', 'wideFit', 'sheer', 'rowBackdrop'],
                        'attributes' => ['style' => 'overflow: hidden;']
                    ]
                );

            if (!empty($outline['options']['_large_bg'])) {
                $node_backdrop->attributes['style'] = 'background: ' .
                    'url(\'https://static.' . $RemoteBase . '/uploads' . $outline['options']['_large_bg'] . '\'); ';
            }

            if (!empty($outline['options']['_video_bg'])) {
                $node_backdrop->nodes[] =
                    $video_container = new HTML(...[
                        'tag'            => 'video',
                        'classes'         => ['videoRowBackdrop', 'tallFit', 'wideFit', 'sheer'],
                        'attributes'    => [
                            'autoplay'    => 'autoplay',
                            'loop'        => 'loop',
                            'muted'        => 'muted',
                        ]
                    ]);
                $video_container->nodes[] =
                    $video_source = new HTML(...[
                        'tag'            => 'source',
                        'attributes'    => [
                            'type'        => 'video/mp4',
                            'src'        => $outline['options']['_video_bg']
                        ]
                    ]);
            }

            if (
                !empty($outline['options']['_overlaytransparency']) &&
                ($outline['options']['_overlaytransparency'] + 0) < 10
            ) {
                $alpha = ($outline['options']['_overlaytransparency'] * 10);
                if (empty($node_backdrop->attributes['style']))
                    $node_backdrop->attributes['style'] = '';
                $node_backdrop->attributes['style'] .= 'opacity: 0.' . $alpha . '; -ms-filter: \'progid:DXImageTransform.Microsoft.Alpha(Opacity=' . $alpha . ')\';'
                    . 'filter: alpha(opacity=' . $alpha . '); -moz-opacity: 0.' . $alpha . '; -khtml-opacity: 0.' . $alpha . ';  ';
                //= 'background-color: '.$outline['options']['_bgcolor'] .'; ';
            }
        }

        $col_index = 0;
        foreach ($descendants as $col) {
            $ColWidth = $outline['col-schema'][$colIndex] . '';

            //if(!empty($col['children']))
            $FrontEndCol = new HTML(...['tag' => 'div', 'classes' => ['col-md-' . $ColWidth, 'layoutColumn']]);
            $node->nodes[] = $FrontEndCol;

            if ($this->InterfaceMode) {
                $FrontEndCol->attributes['data-colindex'] = $col_index;
                $col_index++;
                $col_settings =  isset($col['self']['options']) ? $col['self']['options'] : [];
                $FrontEndCol->attributes['data-container-settings'] =
                    htmlspecialchars(
                        json_encode($col_settings),
                        ENT_QUOTES
                    );
            }

            if (empty($col['children']) && isset($ApproachConfig['EditMode']) && $ApproachConfig['EditMode'] === true) {
                $FrontEndCol->content .= '
				<div class="PsuedoComponent">
					<ul class="Toolbar controls">
						<li class="PlusButton control"
							data-action="Compositor.ComponentLibrary"
							data-role="trigger"
						>
							<i class="bi bi-plus"></i>
						</li>
					</ul>
				</div>';
            }

            foreach ($col['children'] as $ComponentList) {

                // To do, resolve "self", "type" and "template" as ComponentName::BuildParamsFromSelf
                $ComponentName = $ComponentList['self']['type'];
                $cinstance = $ComponentList['self']['instance'];
                $cid =
                    isset($ComponentList['self']) ?
                    (isset($ComponentList['self']['self']) ?
                        $ComponentList['self']['self'] :
                        null
                    ) :
                    null;
                if ($ComponentName == 'LeadGeneration') {
                    $ComponentList['self']['self'] = $cid = $this->OwnerID;
                }

                $options = [];

                $Component = new $ComponentName();
                $datasources =
                    !empty($Component->sources) ?
                    $Component->sources :
                    [
                        strtolower(
                            $Component->getPlural($ComponentName)
                        )
                    ];

                $options['template'] = $SupportPath . '/templates/' . $ComponentName . '.xml';

                if (file_exists($SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml'))
                    $options['template'] = $SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml';

                $PrimaryID = $i = 0;
                $containerTag = isset($Component->ContainerTag) ? $Component->ContainerTag : 'div';

                // TO DO: Separate this if's logic to Component::GenerateBlankComponent($name, $theme='Base')
                if (empty($cid) && $cid !== 0) {
                    $Component = new $ComponentName();
                    //$options['pageID']=$ComponentName.'Editor';
                    $options['classes'] = [$ComponentName, 'Component'];
                    $options['template'] = $SupportPath . '/templates/' . $ComponentName . '.xml';
                    if (file_exists($SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml'))
                        $options['template'] = $SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml';

                    $options['tag'] = $containerTag;
                    $options['attributes'] = [ //'data-self'=>'',
                        // 'data-instance'=>$cinstance,
                        'data-role' => 'Service',
                        // 'data-self'=>$cid,
                        'data-component' => $ComponentName,
                        'data-persist' => '[&quot;data-persist&quot;,&quot;id&quot;,&quot;,&quot;data-instance&quot;,&quot;data-layoutnid&quot;]',
                        'onclick' => 'setActiveComponent(this);'
                        //'data-Context'=>'{&quot;_self_id&quot;:null, &quot;_response_target&quot;:&quot;getSelectedStage()&quot;}',
                        //'data-intent'=>'{&quot;REFRESH&quot;:{&quot;'.$ComponentName.'&quot;:&quot;Save&quot;}}',
                    ];
                    if (!isset($Component->sources))
                        $options[$ComponentName][strtolower($Component->getPlural($ComponentName))] = ['condition' => 'LIMIT 0, 1'];
                    else foreach ($Component->sources as $src)
                        $options[$ComponentName][$src] = ['condition' => 'LIMIT 0, 1'];

                    $FrontEndCol->nodes[] =
                        $tmp = new \Approach\Component\Smart(...$options);
                    // GET FROM COMPONENT OR IMPRINT!!

                    if ($this->InterfaceMode) {
                        $component_options = empty($ComponentList['self']['options']) ? [] : $ComponentList['self']['options'];
                        $tmp->attributes['data-container-settings'] =
                            htmlspecialchars(
                                json_encode($component_options),
                                ENT_QUOTES
                            );
                    }


                    foreach ($tmp->TemplateBinding[$ComponentName] as $dataset => $field_list)
                        foreach ($field_list as $key => $value) {
                            $tmp->data[$ComponentName][$dataset][$key] = $value;
                        }
                }

                // TO DO: Separate this else's logic Component::FetchNamedComponentById()
                else {
                    if (!isset($Component->sources)) {
                        $options[$ComponentName][strtolower($Component->getPlural($ComponentName))] = ['method' => 'WHERE `id` = ' . $cid, 'condition' => ' LIMIT 0, 1'];
                    } else foreach ($Component->sources as $src) {
                        $p_key = $src::$profile['Accessor']['Primary'];
                        if (is_array($cid) && count($cid) == count($Component->sources)) {
                            $options[$ComponentName][$src] = ['target' => $src, 'new_query' => true, 'method' => 'WHERE `' . $p_key . '` = ' . $cid[$i] . ' LIMIT 1'];
                            $PrimaryID = $cid[0];
                            ++$i;
                        } elseif (is_array($cid)) {
                            $options[$ComponentName][$src] = ['target' => $src, 'new_query' => true, 'method' => 'WHERE `' . $p_key . '` = ' . $cid[0] . ' LIMIT 1'];
                            $PrimaryID = $cid[0];
                        } elseif (!empty($cid) || $cid === 0) {
                            $options[$ComponentName][$src] = ['target' => $src, 'new_query' => true, 'method' => 'WHERE `' . $p_key . '` = ' . $cid . ' LIMIT 1'];
                            $PrimaryID = $cid;
                        } else {
                            $options[$ComponentName][$src] = ['target' => $src, 'new_query' => true, 'method' => 'WHERE `' . $p_key . '` = NULL LIMIT 1'];
                            $PrimaryID = null;
                        }
                    }

                    if ($ComponentName == 'StandaloneListing') {
                        $options = [];

                        $ComponentName = 'Listing';
                        $options['template'] = $SupportPath . '/templates/' . $ComponentName . '.xml';
                        if (file_exists($SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml'))
                            $options['template'] = $SupportPath . '/extension/' . static::$CurrentTheme . '.Theme/' . $ComponentName . '.xml';

                        $mode = 0;
                        $cid = $ComponentList['self']['self'];
                        $options['Listing'] = [
                            'listings' => [

                                'queryoverride' => 'CALL GetCompleteListingNormalized(' .
                                    '(SELECT `id` FROM `dd_listings` WHERE `ListingId` = "' . $cid . '"), ' .
                                    $mode . ',' .
                                    $this->OwnerID
                                    . ');',
                                'isProcedure'   =>  true
                            ]
                        ];
                    }

                    if (isset($ComponentList['self']['data'])) {
                        $options['prefetch']['listings'][0] = $ComponentList['self']['data'];
                    }
                    // GET FROM COMPONENT OR IMPRINT
                    $tmp = new HTML(...$options);
                    $FrontEndCol->nodes[] = $tmp;

                    $tmp->classes[] = 'Component';
                    $tmp->classes[] = $ComponentName;
                    $tmp->attributes['data-instance'] = $cinstance;

                    if ($this->InterfaceMode) {
                        $component_options = empty($ComponentList['self']['options']) ? [] : $ComponentList['self']['options'];
                        $tmp->attributes['data-container-settings'] =
                            htmlspecialchars(
                                json_encode($component_options),
                                ENT_QUOTES
                            );
                    }
                }
            }
            $colIndex++;
        }
    }


    public function ResolveComponents(&$DOM)
    {
        $editCount = 0;
        foreach ($DOM->nodes as $child) {
            if ($child instanceof \Approach\Component\Smart) {
                if ($this->InterfaceMode) {
                    if (!in_array($child->tag, HTML::$NoAutoAttributes)) {
                        $child->classes[] = 'Interface controls editable';
                    }

                    // Loop applies logic to each Component INSTANCE rendered as a child of a Smart object
                    // e.g. Each instance of template to render
                    foreach ($child->Context as $WhichComponent => $InstanceContext) {
                        //						$InstanceContext->Edit();
                        //						$child->attributes['data-self'] = /* get self id */;
                        //						$child->attributes['data-component'] = $WhichComponent;

                        $this->ComponentList[$WhichComponent][] = $InstanceContext;
                        $this->editable[$editCount]['name'] = $WhichComponent;
                        $this->editable[$editCount]['index'] = count($this->ComponentList[$WhichComponent]) - 1;
                        $this->editable[$editCount]['reference'] = $child;
                        $editCount++;
                    }
                } else {
                    foreach ($child->Context as $WhichComponent => $InstanceContext) {
                        $this->ComponentList[$WhichComponent][] = $InstanceContext;
                    }
                }
            }
            try {
                if ($child->nodes != null) $this->ResolveComponents($child);
            } catch (\Exception $e) {
                echo PHP_EOL . 'Error resolving components of: ' . PHP_EOL . var_dump($child, true) . ': ' . PHP_EOL . $e . PHP_EOL;
            }
        }
    }

    function publish($silent = false)
    {
        global $RegisteredScripts;

        global $ApproachDebugConsole;
        global $ApproachDebugMode;

        $runOnceA = true;
        // $this->ResolveComponents($this->DOM);

        $count = [];

        foreach ($this->ComponentList as $ComponentInstance => $Instances) {
            if (empty($count[$ComponentInstance]))
                $count[$ComponentInstance] = 0;

            foreach ($Instances as $Context) {
                $op = [];
                $Component = new $ComponentInstance();
                if (isset($Context['prefetch']))
                    $op = ['prefetch' => $Context['prefetch']];
                $Component->createContext($Context['self'], $Context['render'], $Context['data'], $Context['template'], $op);
                $Component->Load($Context['options']);
                $Context['self']->attributes['data-component'] = $ComponentInstance;
                $Context['self']->classes[] = 'ComponentGroup';

                if (!empty($Context['self']->tokens['_self_id'])) {
                    $Context['self']->attributes['data-component-group'] = $count[$ComponentInstance];
                }
                $count[$ComponentInstance]++;
            }
        }
        foreach ($this->editable as &$editableFeature) {
            $references = array();
            if ($editableFeature['reference']->nodes != null) {
                foreach ($editableFeature['reference']->nodes as $child) {
                    $child->classes[] = 'editable';
                    $references[] = $child->pageID;
                    //$internal_references[]=$child->id;
                }
            }
            $editableFeature['reference'] = [];
            $editableFeature['reference'] = $references;    //Links to child template's $tokens['__self_id']

        }

        //$json=json_encode($this->editable);
        //RegisterJQueryEvent('BUBBLE_CLASS_CLICK','editableFeature',$SettingsServiceCall);
        //RegisterJQueryEvent('BUBBLE_ID_CLICK','ApproachControlUnit',$UpdateServiceCall.PHP_EOL.$PreviewServiceCall);
        //RegisterScript("",true,"ToFeatureEditor");
        //CommitJQueryEvents();

        //Get Body, Add Scripts to it
		if(false)		// to do upgrade to approach 2.0
        foreach ($this->DOM->nodes as $child) {
            if ($child->tag == 'body') {
                if ($ApproachDebugMode)
                    $child->nodes[] = $ApproachDebugConsole;
                $child->nodes[] = $RegisteredScripts;
                break;
            }
        }

        // TO DO: Split function, above should be "Composition::prePublish()" or "Composition::Resolve()"
        /*  Output Header	*/
        header('Access-Control-Allow-Origin: *');

        $scope_meta = [];//end(Composition::$Active->Context['entry']);
        $selector_type = !empty($scope_meta['selector_type']) ? $scope_meta['selector_type'] : '';
        // $selector_range = !empty($scope_meta['selector_range']) ? $scope_meta['selector_range'] : '';

        switch ($this->access_mode) {
            case self::PUBLISH_JSON:
                $out = [];
                foreach ($this->ComponentList[$selector_type] as $Context) {
                    foreach ($Context['self']->nodes as $instance) {
                        foreach ($instance->data[$selector_type] as $k => $v) {
                            if (!isset($out[$selector_type][$k]))
                                $out[$selector_type][$k] = [];
                            $out[$selector_type][$k][] = $v;
                        }
                    }
                }

                print_r(
                    json_encode(
                        $out
                    )
                );
                break;
            case self::PUBLISH_API:
                $out = [
                    'SERVE' => []
                ];
                foreach ($this->ComponentList[$selector_type] as $Context) {
                    $out['SERVE'][] = $Context['self']->render() . PHP_EOL;
                }

                print_r(
                    json_encode(
                        $out
                    )
                );
                break;

            case self::PUBLISH_EMBED:
                $out = '';
                foreach ($this->ComponentList[$selector_type] as $Context) {
                    $out .= $Context['self']->render() . PHP_EOL;
                }
                print_r($out);
                break;
            case self::PUBLISH_FULL:
                print_r('<!DOCTYPE html>' . PHP_EOL );
				echo $this->DOM->render();
                break;
            case self::PUBLISH_SILENT:
                if (!empty($this->options['toFile'])) {
                    self::toFile(
                        $this->options['toFile'],
                        $this->DOM->render()
                    );
                } else $this->DOM->render();
                break;
        }
    }
}
