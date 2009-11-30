<?php
class CoreTemplate implements TemplateInterface{
  
  public $controller=false;
  public $indentifier=false;
  //folders to look in
  public $folders=array(); //pulled from the config class
  
  public function __construct($controller, $init=true){
    if($init){
      $this->controller=$controller;
      $this->folders = array_merge($this->folders, Config::$settings['view_directories']);
    }
  }
  
  public function indentifier(){
    $ident="";
    if($name = $this->controller->controller){
      $name = str_replace("Controller", "", $name);
      foreach(split("/[A-Z]/",$name) as $val) $ident .= strtolower($val)."/";
      $ident.= $this->controller->action. $this->controller->format;
    }
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
  
}
?>