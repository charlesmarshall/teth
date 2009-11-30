<?
/**
 * Plugin based framework
 *  Typical site
 *    - app
 *      - config
 *      - controller
 *    - plugins
 *      - plugin_folder
 *    - public
 *      - stylesheets
 *      - javascript
 *    - tmp
 */


/**
 * CONFIG CLASS
 */
class Config{
  public static $settings=array();
}
/**
 * List of default classes for main things
 */
Config::$settings['classes']['autoloader']                    = array('class'=>'Autoloader');
Config::$settings['classes']['application']                   = array('class'=>'CoreApplication', 'component'=>'core', 'module'=>'applications');
Config::$settings['classes']['router']                        = array('class'=>'CoreRouter', 'component'=>'core', 'module'=>'routers');
Config::$settings['classes']['recursive_directory_iterator']  = array('class'=>'ModifiedRecursiveDirectoryIterator', 'component'=>'core', 'module'=>'iterators');
Config::$settings['classes']['ini_directory_iterator']        = array('class'=>'ModifiedRecursiveDirectoryIterator', 'component'=>'core', 'module'=>'iterators');
/**
 * Exceptions
 */
Config::$settings['exceptions']['missing_class']  = array('class'=>'MissingClassException', 'component'=>'core', 'module'=>'exceptions', 'base'=>FRAMEWORK_DIR);
Config::$settings['exceptions']['page_not_found'] = array('class'=>'PageNotFoundException', 'component'=>'core', 'module'=>'exceptions', 'base'=>FRAMEWORK_DIR);
/**
 * Config files
 */
Config::$settings['config']['application'] = array('file'=>'config', 'suffix'=>'.php', 'path'=>CONFIG_DIR);
/**
 * Preset error pages
 */

Config::$settings['error_pages']['generic'] = array('file'=>'error', 'suffix'=>'.html', 'path'=>FRAMEWORK_DIR."/core/exceptions/error-pages/");
/**
 * Directories to read over - app using plugins can register alternative locations for this
 * for example:
 *   SITE_DIR/plugins/ to find all extra plugins
 */
Config::$settings['listings'] = array(FRAMEWORK_DIR);
/**
 * name of ini files
 */
Config::$settings['ini_file'] = "ini.php";
/**
 * static array for pre init hooks, so actions such as cache can be bypass loading
 * - takes form of:
 *  -path to file
 *   - class
 *    - functions
 * These function should load in their own dependancies - to avoid auto loader issues
 */
Config::$settings['pre_functions'] = array();
/**
 * ip address for local environments
 */
Config::$settings['local_environments'] = array("127.0.0.1");
/**
 * Mapping for urls
 * - takes this format: array('controller'=>'PageController', 'action'=>'index');
 * Gets merged into the Router class
 */
Config::$settings['position_map']=false;
/**
 * Default file extension and key used for mime type (minus the .)
 */
Config::$settings['default_format']=".html";
/**
 * An array of locations to look for layouts and views
 */
Config::$settings['view_directories'] = array(APP_DIR."view/");
/**
 * So you can customise before & after rendering calls
 */
Config::$settings['controller_before_action'] = "before";
Config::$settings['controller_after_action'] = "after";

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
   * array containing all top level modules
   */
  public static $components = array();  
  
  public static $classes = array();
  public static $loaded = array();
  public static $excluded = array('index.php','config.php','production.php', 'development.php', 'environment.php');
  /**
   * Work out the correct file path to use from the config file
   */
  public static function path_to($type, $segment='classes'){    
    $conf = Config::$settings[$segment];
    if(!is_array($conf[$type]) || !$conf[$type]['class']) return false;
    if(!$suffix = $conf[$type]['suffix']) $suffix=".php";
    if(!$path = $conf[$type]['base']) $path = FRAMEWORK_DIR;
    if($conf[$type]['component']) $path .= $conf[$type]['component']."/";
    if($conf[$type]['module']) $path .= $conf[$type]['module']."/";
    $path.= $conf[$type]['class'].$suffix;
    return $path;
  }
  /**
   * return name of the class to use for the type requested
   */
  public static function class_for($type, $segment='classes'){
    $conf = Config::$settings[$segment];
    return $conf[$type]['class'];
  }
  /**
   * Look for the class name in the config var, return the key if found
   */
  public static function class_in_config($classname, $segment='classes'){
    $conf = Config::$settings[$segment];
    foreach($conf as $key=>$type){
      if($type['class'] == $classname) return $key;
    }
    return false;
  }
 
  /**
   * Run over the pre init hooks - cache would a good one 
   * 
   * Mainly written so when including a cache component you can bypass loading of most of framework; this point you have only 
   * read in ini files
   */
  public static function pre_init_hooks(){
    foreach(Config::$settings['pre_functions'] as $path=>$classes){
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
    Autoloader::$loaded['Autoloader'] = Autoloader::path_to('autoloader');
    Autoloader::register_inis();
    Autoloader::pre_init_hooks();
    Autoloader::register_classes();
  }
  /**
   * scan over the directories and look for ini.php files 
   * 
   */
  public static function register_inis(){
    $iterator_class = Autoloader::class_for('ini_directory_iterator');
    if(!Autoloader::$loaded[$iterator_class]) Autoloader::load($iterator_class);
            
    $scan = array_reverse(Config::$settings['listings']); //so newer added directories have priority
    foreach($scan as $dir){
      //iterator
      $recurse = new RecursiveIteratorIterator(new $iterator_class($dir), true);
      //loop over it
      foreach($recurse as $item){
        $path = $item->getPathName();
        $name = $item->getFileName();
        if($name == Config::$settings['ini_file'] && is_readable($path) ){
          $dirname = dirname($path);
          include $path;
          $compname = substr($dirname, strrpos($dirname, "/")+1);          
          if(!Autoloader::$components[$compname]) Autoloader::add_component($compname, str_replace("/".$compname, "", $dirname));          
        }
      }
    } 
    
  }
  /**
   * Recursively find all files and load them in to an array
   */
  public static function register_classes($scan=false){
    $iterator_class = Autoloader::class_for('recursive_directory_iterator');
    //load in the interator
    if(!Autoloader::$loaded[$iterator_class]) Autoloader::load($iterator_class);
    //include all the classes found within the Autoloader::$listing folders
    if(!$scan) $scan = array_reverse(Autoloader::$components);
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
        if(strstr($file, ".php") && !Autoloader::$classes[$classname] && $basename != Config::$settings['ini_file'] && !in_array($basename, Autoloader::$excluded) ) Autoloader::$classes[$classname] = $file->getPathName();
      }
    }    
  }
  /**
   * Add components to be registered in the here
   */
  public static function add_component($component_name, $dir=false){
    if(!$dir) $dir=FRAMEWORK_DIR;
    Autoloader::$components[$component_name] = $dir . $component_name;
  }
  /**
   * remove a component
   */
  public static function remove_component($component_name){
    unset(Autoloader::$components[$component_name]);
  }
  /**
   * Load classes in 
   * - use a loaded array to avoid overhead of include_once
   */
  public static function load($classname){
    if(!Autoloader::$loaded[$classname] && Autoloader::$classes[$classname]){
      Autoloader::$loaded[$classname] = Autoloader::$classes[$classname];
      if(is_readable(Autoloader::$classes[$classname])) include Autoloader::$classes[$classname];
    }else if($config_key = Autoloader::class_in_config($classname)){
      Autoloader::$loaded[$classname] = Autoloader::$classes[$classname] = Autoloader::path_to($config_key);
      if(is_readable(Autoloader::$classes[$classname])) include Autoloader::$classes[$classname];
    }else if(!Autoloader::$classes[$classname]){
      $exception_class = Autoloader::class_for('missing_class', 'exceptions');
      $exception_path = Autoloader::path_to('missing_class', 'exceptions');
      include_once $exception_path;
      throw new $exception_class("CLASS NOT FOUND - $classname");
      exit;
    }
  }
  /**
   * Find all the controller files that are in the classes array
   */
  public static function fetch_controllers(){
    $found = array();
    foreach(Autoloader::$classes as $class=>$path) if(strstr($path, CONTROLLER_DIR)) $found[$class] = $path;
    return $found;
  }
  /**
   * Load the application class if it hasn't been 
   * Load the config file
   */
  public static function go(){    
    $all_controllers = array();
    $application = Autoloader::class_for('application');
    if(!Autoloader::$loaded[$application]) Autoloader::load($application);    
    if(defined('SITE_NAME')){
      $configs = $config = Config::$settings['config'];
      foreach($configs as $conf) if(is_readable($conf['path'].$conf['file'].$conf['suffix']) ) include $conf['path'].$conf['file'].$conf['suffix'];
      Autoloader::add_component(SITE_NAME, SITE_DIR);  
      Autoloader::register_classes(array(SITE_DIR));
      $all_controllers = Autoloader::fetch_controllers();
    }

    $run = new $application($all_controllers, true);
  }
  
}

?>