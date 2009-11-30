<?
class CoreController implements ControllerInterface{
  
  //default route items
  public $controller=false;
  public $action=false;
  public $uid=false;
  public $format=false;
  //file name of the view to be rendered
  public $view="index";
  public $layout="application";
  
  public function __construct($route, $init=true){    
    foreach($route as $key=>$val) $this->$key = $val;
    if($init) $this->init();
  }
  
  protected function before(){}
  protected function after(){}  
  protected function init(){}
  
  protected function view(){
    $view = new CoreView($this);
    if(!$this->view = $view->indentifier()) throw new NoViewFoundException("No View Found for - {$this->controller}->{$this->action}");
    
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