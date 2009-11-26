<?
class CoreRouter implements RouterInterface{
  //the var that handles the mapping
  public $position_map=array('controller'=>0, 'action'=>1, 'uid'=>2);
  //default values
  public $mapped=array('controller'=>'PageController', 'action'=>'index');
  //what to split the url by
  public $separator="/";
  //storage
  public $controllers=array();
  public $requested_url=false;
  public $split=array();
  public $get=array();
  public $post=array();

  public function __construct($controllers, $path, $get, $post, $position_map=false){
    $this->controllers = $controllers;
    $this->requested_url = $path;
    $this->get = $get;
    $this->post = $post;
    if(is_array($position_map)) $this->position_map = $position_map;
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
    $map = $this->mapped;
    $position_map = $this->position_map;
    //trim down the url to remove empty array records
    $path = ltrim($this->requested_url, $this->separator);
    if(strlen($path)) $this->split = explode($this->separator, $path);
    
    $controller_position = $position_map['controller'];
    $action_position = $position_map['action'];
    /**
     * find the controller - if its not found then use the shift function to re order & use the default
     */
    if(!$controller = $this->controller($this->split[$controller_position]) ){
      $controller = $map['controller'];
      $position_map = $this->shift($position_map, $controller_position);
      unset($position_map['controller']);
    }
    $this->mapped['controller'] = $controller;
    //remove this from the map array so doesnt get looped over - already been worked out above
    unset($map['controller']);
    //go over the remaining map and get their values
    foreach($map as $what=>$value){
      $pos = $position_map[$what];
      $this->mapped[$what] = $this->find($what,$pos);
    }
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
   * - slice the items from infront of position
   * - slice the items after position
   * - merge the 2
   * - flip it back for the new positions of each item in the position_map
   */
  public function shift($array, $position){
    $flip = array_flip($array);
    ksort($flip);
    $head = array_slice($flip, 0,$position);
    $tail = array_slice($flip, $position+1);
    $sliced = array_merge($head, $tail);
    $new = array_flip($sliced);
    return $new;
  }

  public function find($what, $position){
    if($this->split[$position]) return str_replace("_"," ",str_replace("-", "_",strtolower($this->split[$position]) ) );
    else return $this->mapped[$what];
  }

  public function controller($check){
    $check = str_replace(" ","",ucwords(str_replace("_"," ",str_replace("-", "_",strtolower($check)))))."Controller";
    if(isset($this->controllers[$check])) return $check;
    else return false;
  }

}
?>