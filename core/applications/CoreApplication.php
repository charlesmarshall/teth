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
  //content to be outputed
  public $content = "";
  
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
    $this->router = new $router_class(TethAutoloader::$controllers, $parsed['path'], $_REQUEST);
    return $this->router->map();
  }
  /**
   * - find the format from routing map & remove the . to get the file extension
   * - as long as the headers have not been set and there are some config values for the file extension
   *   then send that header out
   */
  public function headers(){
    $format = $this->routing_map['format'];
    $ext = str_replace(".","", $format);
    
    if(!headers_sent() && ($mime = Config::$settings['mime_headers'][$ext])){
      foreach($mime as $value) header($value);
    }
  }
  /**
   * main function
   * - runs the hooks & finds out the environment
   * - figure out the routing map
   * - make a new controller and tell it not to run the init
   * - make a data array and call the template render function
   * - call the headers function to output headers
   * - echo & run post_exec hook
   */
  public function exec(){
    $this->original_path = $_SERVER['REQUEST_URI'];
    
    $this->pre_exec();
    $this->environment();
    $this->routing_map = $this->route();
    $this->setup();
    
    $controller = new $this->routing_map['controller'](false);
    $controller_url = $this->routing_map['controller_url'];
    $format = $this->routing_map['format'];
    //data for the template
    $data = array('environment'=>$this->environment, 'original_path'=>$this->original_path);
    TethCoreRouter::$requested_path = $this->original_path;
    $this->content = TethCoreTemplate::render($controller_url."/".$controller->intial_view.$format, $data);
    $this->headers();
    $this->post_exec();
    echo $this->content;
  }
  /**
   * post exec function to loop over post functions array and
   * call the relevant functions
   * -> a good place to save $this->content to cache ..
   */
  public function post_exec(){
    if(!is_array(Config::$settings['post_functions'])) return;
    foreach(Config::$settings['post_functions'] as $path=>$classes){
      foreach($classes as $class=>$functions){
        $obj = new $class;
        foreach($functions as $func) $obj->$func();       
      }      
    }
    
  }

}

?>