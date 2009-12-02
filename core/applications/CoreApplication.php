<?php
/**
 * Skel file that doesn't do much, just calls other things
 */
class CoreApplication implements ApplicationInterface{
  //Default the env to development
  public $environment = "development";
  //the route to call
  public $routing_map = array();
  //the router thats in use
  public $router = false;
  //the url that triggered the application
  public $original_path = "";
  //if pass in true it will automatically run
  public function __construct($init=false){
    if($init) $this->exec();
  }
  /**
   * Work out if this is a local or live environment
   * by comparing it to local environment ip list
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
  /**
   * - parse the url that triggered this application
   * - get router class from the config & create a new instance
   * - return the map 
   */
  public function route(){
    $parsed = parse_url($this->original_path);
    //figure out the routing
    $router_class = Config::$settings['classes']['router']['class'];
    $this->router = new $router_class(Autoloader::$controllers, $parsed['path'], $_GET, $_POST);
    return $this->router->map();
  }
  
  public function headers(){
    $format = $this->routing_map['format'];
    $ext = str_replace(".","", $format);
    
    if(!headers_sent() && ($mime = Config::$settings['mime_headers'][$ext])){
      foreach($mime as $index=>$value) if(is_numeric($index)) header($value);
    }
  }

  public function exec(){
    $this->original_path = $_SERVER['REQUEST_URI'];
    
    $this->pre_exec();
    $this->environment();
    $this->routing_map = $this->route();
    $this->setup();
    
    $controller = new $this->routing_map['controller'](false);
    //data for the template - this way treats it as a layout and not a view/partial .. would be nice if they were all the same
    $data = array('routing_map'=>$this->routing_map, 'environment'=>$this->environment, 'is_layout'=>APP_DIR."view/layouts/");
    $content = CoreTemplate::render($controller->use_layout, $data);
    $this->headers();
    echo $content;
    $this->post_exec();
  }
  //save to cache?
  public function post_exec(){}

}

?>