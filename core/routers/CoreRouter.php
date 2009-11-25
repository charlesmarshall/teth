<?
class CoreRouter implements RouterInterface{
  //the var that handles the mapping
  public $position_map=array('controller'=>0, 'action'=>1, 'uid'=>2, 'language'=>3);
  public $mapped=array('controller'=>'PageController', 'action'=>'index');
  public $separator="/";
  
}
?>