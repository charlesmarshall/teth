<?
class PageNotFoundException extends Exception{
  
  public function __construct($message, $status=500) {
    if(Config::$settings['error_pages'][$status]) $conf = Config::$settings['error_pages'][$status];
    else $conf = Config::$settings['error_pages']['generic'];
    $path = $conf['path'].$conf['file'].$conf['suffix'];
    
    header('HTTP/1.1 '.$status.' Page not found',1,$status);
    header('Status '.$status);
    ob_end_clean();
    if(is_readable($path)) echo file_get_contents($path);
    else echo $message;
    exit;
  }
}
?>