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
  public function __construct($data=false){
    foreach((array) $data as $key=>$val) if($val) $this->$key = $val;
  }
}
?>