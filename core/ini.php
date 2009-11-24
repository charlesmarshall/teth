<?
/**
 * Default stuff happens in here
 * - register this component 
 */
Autoloader::add_component("core");
/**
 * Example pre hook
 * Autoloader::add_pre_hook('Required', 'test', FRAMEWORK_DIR.'core/Required.php');
 */
Autoloader::set_config('controller', 'CoreApplication', 'core', 'controllers');
/**
 * Example on how to change what controller is called, setting the class name to Foo, component folder to 
 * bar & base file location to root - being able to do that is quite naughty, might need to fix that...
 *
 * Autoloader::set_config('controller', 'Foo', 'bar', 'controllers', '/root');
 *
 * This will look for a file /root/bar/controllers/Foo.php
 **/

?>