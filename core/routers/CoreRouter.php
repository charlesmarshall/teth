<?
class CoreRouter implements RouterInterface{
  //the var that handles the mapping
  public $position_map=array('controller'=>0, 'action'=>1, 'uid'=>2, 'language'=>3);
  public $mapped=array('controller'=>'PageController', 'action'=>'index');
  public $separator="/";
  
  public $controllers=array();
  public $requested_url=false;
  public $split=array();
  public $get=array();
  public $post=array();
  
  public function __construct($controllers, $path, $get, $post){
    $this->controllers = $controllers;
    $this->requested_url = $path;
    $this->get = $get;
    $this->post = $post;
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
    else $this->mapped[$what];
  }
  
  public function controller($check){
    $check = str_replace(" ","",ucwords(str_replace("_"," ",str_replace("-", "_",strtolower($check)))))."Controller";
    if(isset($this->controllers[$check])) return $check;
    else return false;
  }
 
}
?>