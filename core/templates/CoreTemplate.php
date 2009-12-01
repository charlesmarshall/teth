<?php
class CoreTemplate implements TemplateInterface{
  
  public $data=false;
  public $indentifier=false;
  //folders to look in
  public $path=false; //the original path to render
  
  public function __construct($data=false, $init=false){
    $this->data = $data;
    if($init) $this->init();
  }
  
  public function init(){
    self::render($this->data->original_path, $this->data);
  }
  
  public function indentifier(){
    $folders = Config::$settings['view_directories'];
    $config = (array) $this->data;
    
    $pattern = '/(?<=\\w)(?=[A-Z])/';
    $base = APP_DIR."view/";
    $parts = preg_split($pattern, str_replace("Controller", "", $config['controller']));
    foreach($parts as $part){
      $base.= strtolower($part)."/";
      $folders[] = $base;
    }
    if($config['is_layout']){
      $folders = array_merge((array) $config['is_layout'], $folders);
      $file = $config['use_layout'].$config['format'];
    }else $file = $config['action'].$config['format'];

    $indentifier = "";
    while(($dir = array_pop($folders)) && !$indentifier) if(is_readable($dir.$file)) $indentifier = $dir.$file;

    return $indentifier;
  }
  
  public function content(){
    $this->indentifier = $this->indentifier();
    ob_start();
    $page_data = (array) $this->data;
    extract($page_data);
    include $this->indentifier;
    return ob_get_clean();
  }
  
  public static function render($path, $data = array()){
    if(is_array($data) && !$data['routing_map']){      
      $parsed = parse_url($path);
      $router_class = Config::$settings['classes']['router']['class'];
      $router = new $router_class(Autoloader::$controllers, $parsed['path']);
      $routing_map = $router->map();      
      foreach($routing_map as $k=>$v) $data[$k] = $v;              
      unset($data['router']);
      $controller_class = $data['controller'];
      $controller = new $controller_class($data); 
    }elseif($data['routing_map']){
      foreach($data['routing_map'] as $k=>$v) $data[$k] = $v;
      unset($data['router'], $data['routing_map']);
      $controller_class = $data['controller'];
      $controller = new $controller_class($data);
    }     
    $content = $controller->execute();  
    
    echo $content;
  }
}
?>