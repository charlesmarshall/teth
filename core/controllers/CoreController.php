<?
class CoreController implements ControllerInterface{
  
  //default route items
  public $controller=false;
  public $action=false;
  public $uid=false;
  public $format=false;
  //file name of the view to be rendered
  public $use_view="index";
  public $use_layout="application";
  
  public function __construct($data, $init=true){   
    foreach($data as $key=>$val) $this->$key = $val;
    if($init) $this->init();
  }
  
  protected function before(){}
  protected function after(){}  
  protected function init(){}
  
  public function application(){}
  public function execute(){
    $before = Config::$settings['controller_before_action'];
    $this->{$before}();    
    
    //call the action
    $this->{$this->action}();
    
    $template = new CoreTemplate($this);
    $this->content = $template->content();
    $after = Config::$settings['controller_after_action'];
    $this->{$after}();
    
    return $this->content;
  }
  
}
?>