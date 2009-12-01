<?php
/**
 * Skel file that doesn't do much, just calls other things
 */
class CoreApplication implements ApplicationInterface{
  /**
   * Default the env to development
   */
  public $environment = "development";

  public $routing_map = array();

  public $router = false;
  
  public $original_path = "";

  public function __construct($init=false){
    if($init) $this->exec();
  }
  /**
   * Work out if this is a local or live environment
   */
  public function environment(){
    if(!defined('ENV')){
      $hostname = gethostbyname($_SERVER["HOSTNAME"]);
      if(!strlen($hostname)) $hostname = gethostbyname($_SERVER["SERVER_NAME"]);
      if(!in_array($hostname, Config::$settings['local_environments'])) $this->environment = "production";
      define('ENV', $this->environment);
    }else $this->environment = ENV;
  }
  //extra hook - post routing
  public function setup(){}
  //ideal place to connect to db
  public function pre_exec(){}

  public function route(){
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    //figure out the routing
    $router_class = Config::$settings['classes']['router']['class'];
    $this->router = new $router_class(Autoloader::$controllers, $parsed['path'], $_GET, $_POST);
    return $this->router->map();
  }

  public function exec(){
    $this->pre_exec();
    $this->environment();
    $this->routing_map = $this->route();
    $this->setup();

    $entry_controller_class = $this->routing_map['controller'];
    $this->entry_controller = new $entry_controller_class($this->routing_map);
    $application_output = CoreTemplate::render($this->entry_controller->layout);
    
    $this->post_exec();
    echo $application_output;
  }
  //save to cache?
  public function post_exec(){}

}

?>