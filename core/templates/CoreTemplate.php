<?php
/**
 * The base level template .. runs the static render function 
 *
 */
class CoreTemplate implements TemplateInterface{
  
  public $data=false; //data passed in for rendering
  public $indentifier=false; //the file to render
  
  public function __construct($data=false){
    $this->data = $data;
  }
  /**
   * takes the view_directories config and tries to  
   * the file that matches the action and returns it
   */  
  public function indentifier(){
    //folders to check in
    $folders = Config::$settings['view_directories'];
    //settings to look at
    $config = (array) $this->data;
    //smart pattern to split words by captial letter and keep the rest - well done sheldon for getting that one
    $pattern = '/(?<=\\w)(?=[A-Z])/';
    //base directory
    $base = APP_DIR."view/";
    //split the controller name
    $parts = preg_split($pattern, str_replace("Controller", "", $config['controller']));
    //loop over and make an array of places to look in
    foreach($parts as $part) $base.= $folders[] = $base.strtolower($part)."/";
    //if this is a layout then add the layout directory to the list of places to look & set the filename
    if($config['is_layout']){
      $folders = array_merge((array) $config['is_layout'], $folders); 
      $file = $config['use_layout'].$config['format'];
    }else $file = $config['action'].$config['format'];
    
    $indentifier = false;
    //clever reverse search (so most specific first) for a readable file that matches the file name
    while(($dir = array_pop($folders)) && !$indentifier) if(is_readable($dir.$file)) $indentifier = $dir.$file;
    //return what it is
    return $indentifier;
  }
  /**
   * called from within the Controller object
   *  - inside the controller obj exec a new template is made
   *  - controller is passed into the construct of the template 
   *  - this function is called
   *  - goes find the file to render 
   *  - cast the data to an array
   *  - use extract so the file has access to this vars
   *  - as buffering is turned on clean and return the result
   */
  public function content(){
    $this->indentifier = $this->indentifier();
    ob_start();
    $page_data = (array) $this->data;
    extract($page_data);
    if($this->indentifier){
      include $this->indentifier;    
      return ob_get_clean();
    }else return false;
  }
  /**
   * static render function to handle partials, views, layouts etc
   * - path is string representing url to render, so "/" or "page/_contact"
   * - the data param needs to be an array
   * - if mapping data is set the just grab the values
   *   - copy data over
   *   - pass it in to controller
   * - if no routing data is passed in then call the router
   *   - use the router to find controller etc
   *   - copy over router vars to data array
   *   - pass that data along to the controller
   * - if its not array return false
   * - call the execute function on the controller
   * - return its results
   */
  public static function render($path, $data = array()){
    if(is_array($data)){
      if($data['routing_map']){
        $routing_map = $data['routing_map'];
        unset($data['routing_map']);
      }else{
        $parsed = parse_url($path);
        $router_class = Config::$settings['classes']['router']['class'];
        $router = new $router_class(Autoloader::$controllers, $parsed['path']);
        $routing_map = $router->map();
      }
      foreach($routing_map as $k=>$v) $data[$k] = $v;
      unset($data['router']);
      $controller_class = $data['controller'];
      $controller = new $controller_class($data); 
    }else return false; 
    return $controller->execute();    
  }
}
?>