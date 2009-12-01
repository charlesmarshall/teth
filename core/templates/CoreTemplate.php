<?php
class CoreTemplate implements TemplateInterface{
  
  public $controller=false;
  public $indentifier=false;
  //folders to look in
  public $folders=array(); //pulled from the config class
  
  public function __construct($controller, $init=true){
    if($init){
      $this->controller = $controller;
      $controller_paths = array();
      if($name = $this->controller->controller){
        $name = str_replace("Controller", "", $name);
        $camel_parts = preg_split('/(?<=\\w)(?=[A-Z])/', $name);
        foreach(Config::$settings['view_directories'] as $view_dir)
          foreach($camel_parts as $i => $val)
            $controller_paths[] = $view_dir.strtolower(implode("/",array_slice($camel_parts,0,$i+1)))."/";
      }
      $this->folders = array_merge(array_reverse($controller_paths), $this->folders, Config::$settings['view_directories']);
    }
  }
  
  public function indentifier(){
    $ident = $this->controller->action.$this->controller->format;
    while(($folder = array_shift($this->folders)) && !$this->indentifier){
      if(is_readable($folder.$ident)) $this->indentifier = $folder.$ident;
    }
    return $this->indentifier;    
  }
  
  public function content(){
    ob_start();
    extract((array)$this->controller);
    include $this->indentifier;
    return ob_get_clean();
  }
  
  public static function render($path, $data = false){
    $parsed = parse_url($path);
    $router_class = Config::$settings['classes']['router']['class'];
    $router = new $router_class(Autoloader::$controllers, $parsed['path']);
    $routing_map = $router->map();
    $controller_class = $routing_map['controller'];
    $controller = new $controller_class($routing_map);
    return $controller->execute();
  }
}
?>