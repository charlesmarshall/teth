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
  
  public function application(){}
  public function execute(){
    $before = Config::$settings['controller_before_action'];
    $this->{$before}();    
    
    //call the action
    $this->{$this->action}();

    $view = new CoreTemplate($this);
    if(!$this->view = $view->indentifier()) throw new NoLayoutFoundException("No Layout Found for - {$this->controller}->{$this->action}");
    else $this->view_content = $view->content();
    
    $after = Config::$settings['controller_after_action'];
    $this->{$after}();
    
    return $this->view_content;
  }
  
}
?>