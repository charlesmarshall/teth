<?
class ModifiedRecursiveDirectoryIterator extends RecursiveDirectoryIterator {
  
  public function hasChildren() {
    if(substr(parent::getFilename(),0,1)==".") return false;
    else return parent::hasChildren();
  }
}
?>