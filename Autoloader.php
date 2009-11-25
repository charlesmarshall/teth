<?
/**
 * Plugin based framework
 *  Typical site
 *    - app
 *      - config
 *      - application
 *    - plugins
 *      - plugin_folder
 *    - public
 *      - stylesheets
 *      - javascript
 *    - tmp
 */



/**
 * MAIN PHP TETH_CONFIG VAR
 */
global $TETH_CONFIG;

$TETH_CONFIG = array(
  'autoloader'                    => array('class'=>'Autoloader'),
  'application'                   => array('class'=>'CoreApplication', 'component'=>'core', 'module'=>'applications'),  
  'router'                        => array('class'=>'CoreRouter', 'component'=>'core', 'module'=>'routers'),
  'recursive_directory_iterator'  => array('class'=>'ModifiedRecursiveDirectoryIterator', 'component'=>'core', 'module'=>'iterators'),
  'ini_directory_iterator'        => array('class'=>'ModifiedRecursiveDirectoryIterator', 'component'=>'core', 'module'=>'iterators'),  
  'missing_class_exception'       => array('class'=>'MissingClassException', 'component'=>'core', 'module'=>'exceptions', 'base'=>FRAMEWORK_DIR),
  'application_config_file'       => array('class'=>'config', 'component'=>'config', 'module'=>false, 'base'=>APP_DIR)
  );
/**
 * Main auto load call
 */
function __autoload($classname) {
  Autoloader::load($classname);
}

/**
 * load in all the assets needed for the framework bit by bit
 * 
 */
class Autoloader{
  /**
   * Directories to read over - app using plugins can register alternative locations for this
   * for example:
   *   SITE_DIR/plugins/ to find all extra plugins
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
   * These function should load in their own dependancies - to avoid auto loader issues
   */
  public static $pre_functions = array();
  
  public static $classes = array();
  public static $loaded = array();
  public static $excluded = array('index.php','config.php','production.php', 'development.php', 'environment.php');
  /**
   * Handlers to look after path creation from values in the TETH_CONFIG array
   */
  /**
   * Work out the correct file path to use from the config file
   */
  public static function path_to($type){
    global $TETH_CONFIG;
    if(!$path = $TETH_CONFIG[$type]['base']) $path = FRAMEWORK_DIR;
    if($TETH_CONFIG[$type]['component']) $path .= $TETH_CONFIG[$type]['component']."/";
    if($TETH_CONFIG[$type]['module']) $path .= $TETH_CONFIG[$type]['module']."/";
    $path.= $TETH_CONFIG[$type]['class'].".php";
    return $path;
  }
  /**
   * return name of the class to use for the type requested
   */
  public static function class_for($type){
    global $TETH_CONFIG;
    return $TETH_CONFIG[$type]['class'];
  }
  /**
   * Update the values in the config array to the new params passed in
   */
  public static function set_config($type, $class, $component=false, $module=false, $base=false){
    global $TETH_CONFIG;
    $TETH_CONFIG[$type] = array('class'=>$class, 'component'=>$component, 'module'=>$module);
    if($base) $TETH_CONFIG[$type]['base']=rtrim($base,"/")."/";
  }
  /**
   * Look for the class name in the config var, return the key if found
   */
  public static function class_in_config($classname){
    global $TETH_CONFIG;
    foreach($TETH_CONFIG as $key=>$type){
      if($type['class'] == $classname) return $key;
    }
    return false;
  }
  /**
   * Add the hook to the array; these functions will be called
   */
  public static function add_pre_hook($class, $function,$path=false){
    if(!$path) $path = FRAMEWORK_DIR.$class.".php";
    self::$pre_functions[$path][$class][]=$function;
  }
  /**
   * Remove a hook from the array
   */
  public static function remove_pre_hook($class, $path=false){
    if(!$path) $path = FRAMEWORK_DIR.$class.".php";
    if(is_array(self::$pre_functions[$path]) && self::$pre_functions[$path][$class]) unset(self::$pre_functions[$path][$class]);
  }
  /**
   * Run over the pre init hooks - cache would a good one 
   * 
   * Mainly written so when including a cache component you can bypass loading of most of framework; this point you have only 
   * read in ini files
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
    self::$loaded['Autoloader'] = self::path_to('autoloader');
    self::register_inis();
    self::pre_init_hooks();
    self::register_classes();
  }
  /**
   * scan over the directories and look for ini.php files 
   * 
   */
  public static function register_inis(){
    $iterator_class = self::class_for('ini_directory_iterator');
    if(!self::$loaded[$iterator_class]) self::load($iterator_class);
            
    $scan = array_reverse(self::$listings); //so newer added directories have priority
    foreach($scan as $dir){
      //iterator
      $recurse = new RecursiveIteratorIterator(new $iterator_class($dir), true);
      //loop over it
      foreach($recurse as $item){
        $path = $item->getPathName();
        $name = $item->getFileName();
        if($name == "ini.php" && is_readable($path) ){
          $dirname = dirname($path);
          include $path;
          $compname = substr($dirname, strrpos($dirname, "/")+1);          
          if(!self::$components[$compname]) self::add_component($compname, str_replace("/".$compname, "", $dirname));          
        }
      }
    } 
    
  }
  /**
   * Recursively find all files and load them in to an array
   */
  public static function register_classes($scan=false){
    $iterator_class = self::class_for('recursive_directory_iterator');
    //load in the interator
    if(!self::$loaded[$iterator_class]) self::load($iterator_class);
    //include all the classes found within the self::$listing folders
    if(!$scan) $scan = array_reverse(self::$components);
    foreach($scan as $dir){
      //recursive loop
      $recurse = new RecursiveIteratorIterator(new $iterator_class($dir), true);
      /**
       * Loop over all the files in the directory..  
       * - include them if its a php file, not an ini file & has not been loaded to classes array already
       */
      foreach($recurse as $file){        
        $basename = basename($file);
        $classname = str_replace(".php","",$basename);
        if(strstr($file, ".php") && !self::$classes[$classname] && $basename != self::$ini_file && !in_array($basename, self::$excluded) ) self::$classes[$classname] = $file->getPathName();
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
  /**
   * Load classes in 
   * - use a loaded array to avoid overhead of include_once
   */
  public static function load($classname){
    if(!self::$loaded[$classname] && self::$classes[$classname]){
      self::$loaded[$classname] = self::$classes[$classname];
      if(is_readable(self::$classes[$classname])) include self::$classes[$classname];
    }else if($config_key = self::class_in_config($classname)){
      self::$loaded[$classname] = self::$classes[$classname] = self::path_to($config_key);
      if(is_readable(self::$classes[$classname])) include self::$classes[$classname];
    }else if(!self::$classes[$classname]){
      $exception_class = self::class_for('missing_class_exception');
      $exception_path = self::path_to('missing_class_exception');
      if(!self::$classes['missing_class_exception']) include $exception_path;
      throw new $exception_class("CLASS NOT FOUND - $classname");
      exit;
    }
  }
  /**
   * Load the controller class if it hasn't been 
   * Load the config file
   */
  public static function go(){
    $controller = self::class_for('controller');
    if(!self::$loaded[$controller]) Autoloader::load($controller);
    if(defined('SITE_NAME')){
      $config = self::class_for('application_config_file');
      if(!self::$loaded[$config]) Autoloader::load($config);
      Autoloader::add_component(SITE_NAME, SITE_DIR);  
      Autoloader::register_classes(array(SITE_DIR));
    }
    $run = new $controller(true);
  }
  
}

?>