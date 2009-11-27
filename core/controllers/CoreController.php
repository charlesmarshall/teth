<?
class CoreController implements ControllerInterface{
  
  //default route items
  public $controller="";
  public $action="";
  public $uid="";
  public $format="";
  
  public $controller_name="";
  
  public function __construct($route, $init=true){    
    foreach($route as $key=>$val) $this->$key = $val;
    if($init) $this->init();
  }
  
  protected function before(){}
  protected function after(){}
  
  protected function init(){
    if($this->controller) $this->controller_name = strtolower(str_replace("Controller","",$this->controller));
  
  protected function view(){
    
  }
  
  protected function layout(){
    
  }
  
  public function execute(){
    $before = Config::$settings['controller_before_action'];
    $this->{$before}();
    
    $this->{$this->action}();
    print_r($this);exit;  
    //fetch view content
    $this->view_content = $this->view();
    //fetch layout
    $this->layout_content = $this->layout();
    
    $after = Config::$settings['controller_after_action'];
    $this->{$after}();
  }
  
}
?>