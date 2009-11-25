<?
class PageNotFoundException extends Exception{
  
  public function __construct($message, $status=500) {
    global $TETH_CONFIG;
    $path = $TETH_CONFIG['404_page']['base'] . $TETH_CONFIG['404_page']['class'] . $TETH_CONFIG['404_page']['suffix'];
    
    header('HTTP/1.1 '.$status.' Page not found',1,$status);
    header('Status '.$status);
    ob_end_clean();
    if(is_readable($path)) echo file_get_contents($path);
    else echo $message;
    exit;
  }
}
?>