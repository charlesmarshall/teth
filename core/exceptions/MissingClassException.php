<?
class MissingClassException extends Exception{
  
  
  public function __construct($message, $status=500, $trace=true) {
    if(!$conf = Config::$settings['error_pages'][$status]) $conf = Config::$settings['error_pages']['generic'];
    
    $path = $conf['path'].$conf['file'].$conf['suffix'];

    header('HTTP/1.1 '.$status.' Application Error',1,$status);
    header('Status '.$status);
    ob_end_clean();
    if(is_readable($path)) echo file_get_contents($path);
    else echo $message;
    if($trace) echo "<br />".$this->getTraceAsString()."<br />IN:".$this->getFile()."<br />LN:".$this->getLine();
    exit;
  }
  
}
?>