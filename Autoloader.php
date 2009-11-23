<?
/**
 * Plugin based framework
 *
 * Base level is simply the Application class, which will have skel function that do nothing
 * 
 */
 
/**
 * DEFINE CONSTANTS
 */
if(!defined("FRAMEWORK_DIR")) define("FRAMEWORK_DIR", dirname(__FILE__)."/");

/**
 * Main auto load call
 */
function __autoload($classname) {
  //Autoloader::load($classname);
}

/**
 * load in all the assets needed for the framework bit by bit
 * 
 */
class Autoloader{
  /**
   * Directories to read over - app using plugins can register alternative locations for this
   * for example:
   *   SITE_DIR/plugins/
   */
  public static $listings = array(FRAMEWORK_DIR);
  /**
   * Ini file that will be read in
   */
  public static $ini_file="ini.php";
  /**
   * array containing all top level modules
   */
  public static $components = array();
  /**
   * static array for pre init hooks, so actions such as cache can be bypass loading
   * - takes form of:
   *  -path to file
   *   - class
   *    - functions
   */
  public static $pre_functions = array();
  
  public static $loaded = array();
  /**
   * Add the hook to the array; these functions will be called 
   * 
   */
  public static function add_pre_hook($class, $function,$path=false){
    if(!$path) $path = FRAMEWORK_DIR.$class.".php";
    else $path=FRAMEWORK_DIR.$path;
    self::$pre_functions[$path][$class][]=$function;
  }
  
  public static function remove_pre_hook($class, $path=false){
    if(!$path) $path = FRAMEWORK_DIR.$class.".php";
    else $path=FRAMEWORK_DIR.$path;
    unset(self::$pre_functions[$path][$class]);
  }
  /**
   * Run over the pre init hooks - cache would a good one 
   */
  public static function pre_init_hooks(){
    foreach(self::$pre_functions as $path=>$classes){
      include $path;
      foreach($classes as $class=>$functions){
        $obj = new $class;
        foreach($functions as $func) $obj->$func();       
      }      
    }
  }
  /**
   * main function that reads a bunch of stuff in
   */
  public static function init(){
    self::$loaded['Autoloader'] = FRAMEWORK_DIR.'Autoloader.php';
    self::register_inis();
    self::pre_init_hooks();
  }
  /**
   * scan over the directories and look for ini.php files 
   */
  public static function register_inis(){
    $scan = self::$listings;
    foreach($scan as $dir){
      $listing = scandir($dir);
      foreach($listing as $item){
        $item = FRAMEWORK_DIR.$item;
        $ini = $item."/".self::$ini_file;
        if(is_dir($item) && is_readable($ini) ) include $ini;
      }
    }    
  }
  /**
   * Recursively find all files and load them in to an array, if 
   */
  public static function register_classes(){
    //load in the interator
    if(!self::$loaded['ModifiedRecursiveDirectoryIterator']){
      self::$loaded['ModifiedRecursiveDirectoryIterator'] = FRAMEWORK_DIR."ModifiedRecursiveDirectoryIterator.php";
      include self::$loaded['ModifiedRecursiveDirectoryIterator'];
    }
    //include all the classes found within the self::$listing folders
    $scan = self::$listings;
    foreach($scan as $dir){
      //recursive loop
      $recurse = new RecursiveIteratorIterator(new ModifiedRecursiveDirectoryIterator($dir), true);
      /**
       * Loop over all the files in the directory..  
       * - include them if its a php file, not an ini file & has not been loaded already
       */
      foreach($recurse as $file){
        $basename = basename($file);
        $classname = str_replace(".php","",$basename);
        if(strstr($file, ".php") && !self::$loaded[$classname] && $basename != self::$ini_file){
          include $file;          
          self::$loaded[$classname] = $file;
        }
      }
    }
    
  }
  /**
   * Add components to be registered in the here
   */
  public static function add_component($component_name, $dir=false){
    if(!$dir) $dir=FRAMEWORK_DIR;
    self::$components[$component_name] = $dir . $component_name;
  }
  /**
   * remove a component
   */
  public static function remove_component($component_name){
    unset(self::$components[$component_name]);
  }
  
  public static function go(){
    
  }
  
}


Autoloader::init();
?>