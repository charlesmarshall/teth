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
    print_r($this);exit;
  }
  
  protected function init(){
    if($this->controller) $this->controller_name = strtolower(str_replace("Controller","",$this->controller));
  }
  
  
}
?>