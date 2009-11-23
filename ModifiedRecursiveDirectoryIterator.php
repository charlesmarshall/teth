<?
class ModifiedRecursiveDirectoryIterator extends RecursiveDirectoryIterator {
  
  public function hasChildren() {
    if(substr($this->getFilename(),0,1)==".") return false;
    else return parent::hasChildren();
  }
}
?>