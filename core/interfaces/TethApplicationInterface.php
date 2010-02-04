<?
interface TethApplicationInterface{
  //called first & calls all others
  public function exec();
  //hook- first thing called in the exec
  public function pre_exec();
  //called to figure env - live, production
  public function environment();
  //figure out what to call
  public function route();  
  //hook - called after the routing
  public function setup();  
  //hook - last thing called in exec
  public function post_exec();
}
?>