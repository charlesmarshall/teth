<?
/**
 * Base controller  
 */
class CoreController implements TethControllerInterface{
  
  //default route items
  public $controller=false;
  public $action=false;
  public $uid=false;
  public $format=false;

  /**
   * initial view to be rendered before other views
   * this view will be triggered by CoreApplication
   * $this->original_path will be set to the original url the application was triggered with
   * normal behaviour is to call a render on that var inside the initial_view
   */
  public $intial_view="application";  
  public function application(){}
  
  /**
   * pass in data to add to the controller
   * if init is true call the init function, replacement for controller global
   */
  public function __construct($data=false){
    foreach((array) $data as $key=>$val) if($val) $this->$key = $val;
  }
}
?>