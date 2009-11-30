<?php
class CoreLayout extends CoreTemplate implements TemplateInterface{
  
  public function __construct($controller, $init=true){
    if($init){
      $this->controller=$controller;
      $this->folders = array_merge($this->folders, Config::$settings['layout_directories']);
    }
  }
  
  public function indentifier(){
    $ident="";
    if($name = $this->controller->layout) $ident.= $this->controller->layout. $this->controller->format;
    while(($folder = array_shift($this->folders)) && !$this->indentifier){
      if(is_readable($folder.$ident)) $this->indentifier = $folder.$ident;
    }
    return $this->indentifier;    
  }
  
}
?>