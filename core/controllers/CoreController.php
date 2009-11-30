<?
class CoreController implements ControllerInterface{
  
  //default route items
  public $controller=false;
  public $action=false;
  public $uid=false;
  public $format=false;
  //folders to look in
  public $folders=array(); //pulled from the config class
  //file name of the view to be rendered
  public $view=false;
  
  public $layout="application";
  
  public function __construct($route, $init=true){    
    foreach($route as $key=>$val) $this->$key = $val;
    if($init) $this->init();
  }
  
  protected function before(){}
  protected function after(){}
  
  protected function init(){}
  
  protected function view(){
    
  }
  
  protected function layout(){
    
  }
  
  
  public function execute(){
    $before = Config::$settings['controller_before_action'];
    $this->{$before}();
    
    //call the function
    $this->{$this->action}();
    //fetch view content
    $this->content_for_layout = $this->view();
    //fetch layout
    $this->layout_content = $this->layout();
    
    $after = Config::$settings['controller_after_action'];
    $this->{$after}();
  }
  
}
?>