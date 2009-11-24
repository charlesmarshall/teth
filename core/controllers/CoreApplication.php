<?
/**
 * Skel file that doesn't do much, just calls other things
 */
class CoreApplication{
  
  public function __construct($init=true){
    if($init) $this->exec();
  }
  
  public function environment(){}
  public function setup(){
    $this->environment();    
  }  
  //ideal place to connect to db
  public function pre_exec(){}
  public function exec(){
    $this->pre_exec();
    $this->setup();    
    $this->post_exec();
  }
  //save to cache?
  public function post_exec(){}
  
}

?>