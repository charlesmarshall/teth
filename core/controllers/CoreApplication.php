<?
/**
 * Skel file that doesn't do much, just calls other things
 */
class CoreApplication{
  /**
   * Default the env to development
   */
  public $environment = "development";
  
  public function __construct($init=false){
    if($init) $this->exec();
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

    exit;
  }
  //load file based on the current environment
  public function setup(){}  
  //ideal place to connect to db
  public function pre_exec(){}
  
  public function exec(){
    $this->pre_exec();
    $this->environment();
    $this->setup();
     
    $this->post_exec();
  }
  //save to cache?
  public function post_exec(){}
  
}

?>