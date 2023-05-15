<?php

/*************************************************************************

 APPROACH 2.0
 (C) 2002 - 2022 Garet Claborn, Tom Samwel
 garet@suiteux.com tom@suiteux.com

 This code is provided under the GPL 2.0 license.
 This code is explicitly not provided under any future version of the license.

 GPL 2.0 License
*************************************************************************/
// require_once __DIR__ . '/Render/Stream.php';
namespace Approach;

const MAJOR_VERSION = 2;
const MINOR_VERSION = 0;
const PATCH_VERSION = -1;

class Scope
{
	const in = 0;
	const out = 1;

	// "Labeled arrays" refer to arrays that are indexed by a constant symbol with integer values
	// Generally an abstract class with a static method "cases()" is used to define the labels
	// Sometimes enums are used instead of abstract classes, but enums are not as flexible yet

	// Labeled Symbols
	public static $Active = NULL;			// The currently active Scope
    public static $Service = [];			// Labeled array of services by label
	public static $triage = [];				// Labeled array of handlers by Exception::getCode()
    public static $context = [];			// Labeled array using enum->value indexes from the context enum

	// Doubly Labeled Arrays 
	public static $decoders		 		    = [ self::in => null, self::out => null ];
	public static $format_encoders			= [ self::in =>[], self::out => [] ];	
	public static $type_casters				= [ self::in =>[], self::out => [] ];
	public static $actions					= [ self::in =>[], self::out => [] ];

    public Render\Node $ErrorRenderable;
	public $type;
    public static $OutputStream;

    /*
    TODO: move to 
    namespace Suitespace{ 
        class SuiteUX extends \Approach\Scope{ 
            .. 
        } 
    }
    */
    public $ContentEditable = [
        'CleanTextEmbed.content',
        'TextEmbed.content',
        'ButtonRow.content',
        'Hero.title',
        'Hero.subtitle'
    ];

    function __construct(
        public array $path = [
            path::project->value    =>  '/srv/local.home',
            path::installed->value    =>  '/srv/local.home/support/lib/approach',
        ],

        public array $deployment = [
            deploy::base->value   =>  'local.home'
        ],
        public runtime $mode = runtime::staging,
        public runtime $state = runtime::staging,
        $OutputStream = 'php://stdout',
        public $project = 'Approach'
    ) {

        Scope::$OutputStream = fopen($OutputStream, 'w');
        $this->ErrorRenderable = new Render\Node(content: ' Sorry, this item is not feeling well today. ');

        foreach (path::cases() as $label) {
            self::$context[context::path->value][$label->value] =
                $path[$label->value] ??
                $label->get($path[path::project->value]);
        }

        foreach (deploy::cases() as $label) {
            self::$context[context::deploy->value][$label->value] =
                $deployment[$label->value] ??
                $label->get($deployment[deploy::base->value]);
        }

        self::$context[context::runtime->value] = $this->mode;

        /**
         * Call static initializers for Approach classes
         * If your project has static initializers, extend Scope and put them in your constructor, then call parent::__construct()
         */

		Render\Node::__static_init();
		Service\Service::__static_init();

        
        Scope::$Active = $this;
    }

    public function __destruct()
    {
        // if( Scope::$Active->??? !== ???::service )   // not running as a service? close open resources
        // fclose(Scope::$OutputStream);
    }

    public static function GetPath(path $label): string
    {
        return Scope::$context[context::path->value][$label->value];
    }

    public static function SetPath(path $label, $p = ''): string
    {
        return Scope::$context[context::path->value][$label->value] = $p;
    }

    public static function GetDeploy(deploy $label): string
    {
        return Scope::$context[context::deploy->value][$label->value];
    }
    
    public static function SetDeploy(deploy $label, $p = ''): string
    {
        return Scope::$Active->context[context::deploy->value][$label->value] = $p;
    }
    
    public static function GetRuntime(): runtime
    {
        return Scope::$Active->mode;
    }
    
    public static function SetRuntime(runtime $label, $p = runtime::development): runtime
    {
        return Scope::$Active->mode = $p;
    }

    public static function GetState(): runtime
    {
        return self::$state;
    }

    public static function SetState(runtime $label, $p = runtime::development): runtime
    {
        return self::$state = $p;
    }
    
    protected function error_out($string, $prefix = ' > '): void
    {
		$this->ErrorRenderable[] = new Render\Node( $prefix . $string . PHP_EOL);
    }

    public function ExportError($obj, $name = 'Approach Error Log'): void
    {
        
        $ConsoleOutput = 
		PHP_EOL . PHP_EOL . 
		' > ::::: BEGIN ' . $name . ' ::::: ' . PHP_EOL.
        	var_export($obj, true).PHP_EOL . 
		' > ::::: END ' . $name . ' ::::: ' . 
		PHP_EOL . PHP_EOL ;
		
		$this->error_out($ConsoleOutput);
    }

    function generateCallTrace(): string
    {
        $e = new \Exception();
        //$trace = explode("\n", $e->getTraceAsString());
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos(var_export($trace[$i]), ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return PHP_EOL . '<!-- ' . PHP_EOL . implode("\n\t", $result) . PHP_EOL . ' -->' . PHP_EOL;
    }


    function LayerError($layer, $content): void
    {
        $this->ErrorRenderable->content = $layer . '() error: ' . PHP_EOL . $content;
        echo $this->ErrorRenderable;
    }

    function Error($content): void
    {
        $this->ErrorRenderable->content = PHP_EOL . $content;
        echo $this->ErrorRenderable;
    }
}
