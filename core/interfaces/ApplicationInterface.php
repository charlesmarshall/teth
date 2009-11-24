<?
interface ApplicationInterface{
  
  public function environment();
  public function setup();
  public function pre_exec();
  public function exec();
  public function post_exec();
}
?>