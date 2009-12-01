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
    
    $this->headers();
    //data for the template - this way treats it as a layout and not a view/partial .. would be nice if they were all the same
    $data = array('routing_map'=>$this->routing_map, 'environment'=>$this->environment, 'is_layout'=>APP_DIR."view/layouts/");
    $template = new CoreTemplate($data, true);
    
    $this->post_exec();
  }
  //save to cache?
  public function post_exec(){}

}

?>