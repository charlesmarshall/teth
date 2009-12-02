<?
/**
 * Base controller  
 */
class CoreController implements ControllerInterface{
  
  //default route items
  public $controller=false;
  public $action=false;
  public $uid=false;
  public $format=false;
  //file name of the layout to be rendered
  public $use_layout="application";
  
  /**
   * pass in data to add to the controller
   * if init is true call the init function, replacement for controller global
   */
  public function __construct($data, $init=true){   
    foreach((array) $data as $key=>$val) if($val) $this->$key = $val;
    if($init) $this->init();
  }
  /**
   * Empty hook function for rendering
   */
  protected function init(){}
  /**
   * main function - warning, templates are smart & recursive...
   * - runs the correct action, this is set values etc
   * - creates a template, passes itself along
   * - call the content function directly
   * - return the result
   */
  public function execute(){
    //call the action
    $this->{$this->action}();
    
    $template = new CoreTemplate($this);
    $this->content = $template->content();
    
    return $this->content;
  }
  
}
?>