<?
class MissingClassException extends Exception{
  
  public static $error_page = false;
  
  public function __construct($message, $status=500) {
    header('HTTP/1.1 '.$status.' Application Error',1,$status);
    header('Status '.$status);
    ob_end_clean();
    if(self::$error_page) echo file_get_contents(self::$error_page);
    else echo $message;
    exit;
  }
}
?>