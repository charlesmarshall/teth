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