<?
/**
 * Static array to contain all the classes that
 * are needed
 */
class Required{
  /**
   * multi dimensional array
   *  index is plugin
   *    value is an list of paths to class
   */
  public static $load=array();
  
  public static function component($component){
    self::$load[$component][] = 'all';
  }
  
  public static function classname($component, $class, $path){
    self::$load[$component][$class] = $path;
  }
  
}

?>