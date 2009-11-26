<?
/**
 * Skel file that doesn't do much, just calls other things
 */
class CoreApplication implements ApplicationInterface{
  /**
   * Default the env to development
   */
  public $environment = "development";

  public $available_controllers = array();

  public $controller = false;

  public $router = false;

  public function __construct($available_controllers=array(), $init=false){
    $this->available_controllers = $available_controllers;
    if($init) $this->exec();
  }
  /**
   * Work out if this is a local or live environment
   */
  public function environment(){
    if(!defined('ENV')){
      $hostname = gethostbyname($_SERVER["HOSTNAME"]);
      if(!strlen($hostname)) $hostname = gethostbyname($_SERVER["SERVER_NAME"]);
      $dns = dns_get_record(gethostbyaddr($hostname));
      if(count($dns)) $this->environment = "production";
      define('ENV', $this->environment);
    }else $this->environment = ENV;
  }
  //load file based on the current environment
  public function setup(){}
  //ideal place to connect to db
  public function pre_exec(){}

  public function route(){
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    //figure out the routing
    $router_class = $this->config['router']['class'];
    $this->router = new $router_class($this->available_controllers, $parsed['path'], $_GET, $_POST);
    $map = $this->router->map();
    
    
  }

  public function exec(){
    $this->pre_exec();

    $this->environment();

    $this->route();

    $this->setup();

    $this->post_exec();
  }
  //save to cache?
  public function post_exec(){}

}

?>