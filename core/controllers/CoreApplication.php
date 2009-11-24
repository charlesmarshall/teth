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
  /**
   * Work out if this is a local or live environment
   */
  public function environment(){
    $hostname = gethostbyname($_SERVER["HOSTNAME"]);
    if(!strlen($hostname)) $hostname = gethostbyname($_SERVER["SERVER_NAME"]);
    $dns = dns_get_record(gethostbyaddr($hostname));
    if(count($dns)) $this->environment = "production";
    if(!defined('ENV')) define('ENV', $this->environment);

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