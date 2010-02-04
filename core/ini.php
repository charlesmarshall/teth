<?
/**
 * Default stuff happens in here
 * - register this component 
 */
TethAutoloader::add_component("core");

/**
 * Changing a default class
 * -> Config::$settings['classes']['recursive_directory_iterator'] = array('class'=>'NewDirectoryIterator', 'component'=>'core', 'module'=>'iterators', 'base'=>'/path/to/folder/');
 * 
 * Changes recursive_directory_iterator class details. Looks for a file called NewDirectoryIterator.php in '/path/to/folder/' + 'core/' + 'iterators/'
 * then uses NewDirectoryIterator as the class name to create
 */
/**
 * Changing an exception
 * -> Config::$settings['exceptions']['page_not_found'] = array('class'=>'PageNotFoundException', 'component'=>'core', 'module'=>'exceptions', 'base'=>FRAMEWORK_DIR);
 * 
 * Changes the page_not_found exception details. File name will be PageNotFoundException.php, location will be FRAMEWORK_DIR . 'core/' .  'exceptions/'
 * and the class name will be PageNotFoundException
 */
/**
 * Changing the application config file
 * -> Config::$settings['config']['application'] = array('file'=>'config', 'suffix'=>'.php', 'path'=>"/path/to/config/folder/");
 * Looks for the file called config.php in /path/to/config/folder/  
*/
/**
 * Changing an error page
 * -> Config::$settings['error_pages']['503'] = array('file'=>'503', 'suffix'=>'.html', 'path'=>"/path/to/config/folder/");
 * Looks for the file called 503.html in /path/to/config/folder/  
 */  
/**
 * Adding a new place to find ini files
 * -> Config::$settings['listings'][] = "/path/to/folder/";
 * -> Config::$settings['listings'][] = "/path/to/application/plugin/folder/"; 
 * These will be scanned for ini files
 */
/**
 * Changing name of the ini file
 * -> Config::$settings['ini_file'] = "whatever.php";
 * Will be look for "whatever.php" as ini files
 */  
/**
 * Example pre hook
 * -> Config::$settings['pre_functions']['/full/path/to/folder/ClassName.php']['ClassName'][] = 'function_name';
 *
 * File /full/path/to/folder/ClassName.php will be loaded, class called ClassName will be created & 'function_name'
 * will be called
 */

?>