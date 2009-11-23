<?
/**
 * Interface to make sure that classes that need other classes
 * to be loaded first have a common function for calling
 *
 * Idea behind this is that all files are scanned in and this method called
 * creating a static array with all files in, these are then read in before
 * anything else 
 */
interface RequirementEngine{
  
  protected function require(){}
  
}
?>