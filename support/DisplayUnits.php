<?php

//require_once('../Service.php');
namespace Approach\Render\Props;

use \Approach\Render;
use Approach\Render\HTML;
//require_once(__DIR__ . '/../DataObject.php');

$ApproachDisplayUnit = array();

class UserInterface extends HTML
{
  public $Layout;
  public $Header;   //in layout
  public $Titlebar; //in header
  public $Content;	//in layout
  public $Footer;	//in layout
  public $title='';

  function __construct()
  {
    $this->tag	        = 'ul';
    $this->classes[]	= 'Interface';
    $this->nodes[]	= $this->Layout = new HTML('li','',	array('classes'=>'InterfaceLayout') );

    $this->Layout->nodes[]	= $this->Header = new HTML('ul','',	array('classes'=>array('Header','controls')));
    $this->Layout->nodes[]	= $this->Content	= new HTML('ul','',	array('classes'=>array('Content','controls')));
    $this->Layout->nodes[]	= 
      $this->Footer	= new HTML('ul','',	array('classes'=>array('Footer','controls')));

    $this->Header->nodes[]	= $this->Titlebar	= new HTML('li','',	array('classes'=>array('Titlebar'),'content'=>($this->title | 'Command Console')));
  }
}

class Wizard extends UserInterface
{
  public $Slides;
  public $CancelButton;
  public $BackButton;
  public $NextButton;
  public $FinishButton;

  function __construct()
  {
    parent::__construct();
    
    $this->title = 'Complete actions using the following steps';
    $this->classes[]	= 'Wizard';
    
    
    $CancelButton	= new HTML('li','',	['classes'=>['Cancel',	'DarkRed',	'Button'],'content'=>'Cancel']);
    $BackButton	= new HTML('li','',	array('classes'=>array('Back',	'DarkGreen',	'Button'),'content'=>'Back'));
    $NextButton	= new HTML('li','',	array('classes'=>array('Next',	'DarkGreen',	'Button'),'content'=>'Next'));
    $FinishButton	= new HTML('li','',	array('classes'=>array('Finish',	'DarkBlue',	'Button'),'content'=>'Finish'));

    $this->Footer->nodes[]	= $CancelButton;
    $this->Footer->nodes[]	= $BackButton;
    $this->Footer->nodes[]	= $NextButton;
    $this->Footer->nodes[]	= $FinishButton;
    $FinishButton->attributes['data-intent']='Autoform Insert ACTION';
    
  }
}

class BootstrapPanel {
    private static $panelID=0;
    public $panel;
    public $titlebar;
    public $body;
    public $footer;
    
    function __construct($title='', $body='', $footer='')
    {
        $arg=[];
        $arg['tag'] = 'div';
        $arg['classes'] = ['col-sm-6'];
        $arg['pageID'] = 'BootPanel__' . BootstrapPanel::$panelID;
        $this->panel = new HTML(...$arg);    //Set $this->panel
        BootstrapPanel::$panelID++;        
        
        $arg['classes'] = ['panel-group'];
        $panel_group= new HTML(...$arg);
        
        $arg['classes'] = ['panel panel-default'];
        $panel_default = new HTML(...$arg);
        
        $arg['classes'] = ['panel-heading'];
        $panel_heading = new HTML(...$arg);
        
        $arg['classes'] = ['panel-collapse collapse'];
        $panel_collapse = new HTML(...$arg);
        
        if(gettype($title) == 'string')
        {
            $arg['tag'] = 'h4';
            $arg['classes'] = ['panel-title'];
            $arg['content'] = $title;
            $this->titlebar =  new HTML(...$arg);
        }
        else $this->titlebar = $title;

        if(gettype($body) == 'string')
        {
            $arg['tag'] = 'div';
            $arg['classes'] = ['panel-body'];            
            $arg['content'] = $body;
            $this->body = new HTML(...$arg);
        }
        else $this->body = $body;

        if(gettype($footer) == 'string')
        {
            $arg['tag'] = 'div';
            $arg['classes'] = ['panel-footer'];
            $arg['content'] = $footer;
            $this->footer = new HTML(...$arg);
        }
        else $this->footer = $body;

        $this->panel->nodes[] = $panel_group;
        $panel_group->nodes[] = $panel_default;
        $panel_default->nodes[] = $panel_heading;
        $panel_heading->nodes[] = $this->titlebar;
        
        $panel_default->nodes[]= $panel_collapse;
        $panel_collapse->nodes[]= $this->body;
        $panel_collapse->nodes[]= $this->footer;
    }
}


$ApproachDisplayUnit['Composition']['NewWizard'] = new Wizard();
$ApproachDisplayUnit['User']['Browser'] = new HTML('ul');
$ApproachDisplayUnit['Bootstrap']['Panel'] = new BootstrapPanel('My Panel', 'Content', 'Footer');




?>