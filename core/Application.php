<?
/**
 * Skel file that doesn't do much, just calls other things
 */
class Application{
  
  public function __construct($init=true){
    if($init) $this->exec();
  }
  
  public function environment(){}
  public function setup(){
    $this->environment();    
  }  
  public function pre_exec(){}
  public function exec(){
    $this->pre_exec();
    $this->setup();    
    $this->post_exec();
  }
  public function post_exec(){}
  
  /**
   * Does nothing, as this class doesn't need
   * anything in order to work
   */
  protected function require(){}
}

?>