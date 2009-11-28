<?
class CoreRouter implements RouterInterface{
  //the var that handles the mapping
  public $position_map=array('controller'=>0, 'action'=>1, 'uid'=>2);
  //default values - format is appended and based on config var
  public $mapped=array('controller'=>'PageController', 'action'=>'index');
  //what to split the url by
  public $separator="/";
  //storage
  public $controllers=array();
  public $requested_url=false;
  public $get=array();
  public $post=array();

  public function __construct($controllers, $path, $get=array(), $post=array()){
    $this->controllers = $controllers;
    $this->requested_url = $path;
    $this->get = $get;
    $this->post = $post;
    if(is_array(Config::$settings['position_map'])) $this->position_map = Config::$settings['position_map'];
  }
  /**
   * Mapping function to determine what controller
   * should be called from the url passed in
   * - find the controller
   * - find the rest of the items in the position_map
   * - check the action exists as a public method on that controller
   *   - if it doesn't, 404
   *   - if it does return values
   *
   */
  public function map(){
    $position_map = $this->position_map;
    //trim down the url to remove empty array records
    $path = $this->substitute(ltrim($this->requested_url, $this->separator));  
    if(strlen($path)) $split = array_filter(explode($this->separator, $path)); //explode & remove blanks
    else $split = array();
    //first off, find the controller.. look at position
    $controller_pos = (array) $position_map['controller'];
    //take a slice from the first part of the controller
    $chunk = array_slice($split,$controller_pos[0]);
    //var for class name
    $controller=false;
    //string for all of it
    $cumlative="";
    $depth=0;
    // while not found a controller and still stuff to check, loop over 
    while(($check = array_shift($chunk)) && !$controller){
      //amend the strings ready for checking
      $cumlative .= $check = str_replace(" ", "_", ucwords(str_replace("_", " ", $check)));
      //check for current level first
      if($this->controllers[$check."Controller"]) $controller = $check."Controller";
      //check everything so far..
      else if($this->controllers[$cumlative."Controller"]) $controller = $cumlative."Controller";
      $depth++;
    }
    //if the controller was found 
    if($controller){
      $this->mapped['controller'] = $controller;
      array_splice($split, $controller_pos[0],$depth);
    }
    //reorder the position map
    $position_map = $this->shift($position_map, $controller_pos[0]);
    
    unset($position_map['controller']);
    
    foreach($position_map as $what=>$value) if($found = $split[$position_map[$what]]) $this->mapped[$what] = $found;     
    //if no controller, action or method on that class then die
    if(!$this->mapped['controller'] || !$this->mapped['action'] || !method_exists($this->mapped['controller'], $this->mapped['action']))
      throw new PageNotFoundException("Page Not Found");
    else{
      $reflect = new ReflectionMethod($this->mapped['controller'], $this->mapped['action']);
      //if this isnt a public method then throw an error
      if(!$reflect->isPublic()) throw new PageNotFoundException("Page Not Found");
    }     
          
    return $this->mapped;
  }
  /**
   * Function designed to remove an item from the position_map
   * - flips the array
   * - sort the array by key
   * - splice the item at position out of the array
   * - flip it back for the new positions of each item in the position_map
   */
  public function shift($array, $position, $length=1){
    $flip = array_flip($array);
    ksort($flip);
    array_splice($flip, $position,$length);
    $new = array_flip($flip);
    return $new;
  }

  public function find($what, $position, $in){
    $check = $in[$position];
    if(strstr($check, $this->mapped['format'])) $check = str_replace($this->mapped['format'], "", $check);
    if($in[$position]) return str_replace("_"," ",str_replace("-", "_",strtolower($check) ) );
    else return $this->mapped[$what];
  }

  public function controller($check){
    if(strstr($check, $this->mapped['format'])) $check = str_replace($this->mapped['format'], "", $check);
    $check = str_replace(" ","",ucwords(str_replace("_"," ",str_replace("-", "_",strtolower($check)))))."Controller";
    if(isset($this->controllers[$check])) return $check;
    else return false;
  }
  
  public function format($check){
    if(strlen($check) && ($pos = strrpos($check,".") ) ) return substr($check,$pos);
    else return Config::$settings['default_format'];
  }


  public function substitute($original, $pattern="/[^a-zA-Z0-9\s\/]/", $replace="_"){
    return strtolower(preg_replace($pattern, $replace, $original));
  }
}
?>