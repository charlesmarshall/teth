<?
class MissingClassException extends Exception{
  
  
  public function __construct($message, $status=500) {
    global $TETH_CONFIG;
    $path = $TETH_CONFIG['500_page']['base'] . $TETH_CONFIG['500_page']['class'] . $TETH_CONFIG['500_page']['suffix'];
    
    header('HTTP/1.1 '.$status.' Application Error',1,$status);
    header('Status '.$status);
    ob_end_clean();
    if(is_readable($path)) echo file_get_contents($path);
    else echo $message;
    exit;
  }
  
}
?>