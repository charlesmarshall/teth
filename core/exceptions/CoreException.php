<?
class CoreException extends Exception{  
  public $status = 500;
  public $message = "Application Error";
  
  public function __construct($message="", $status=false, $trace=true) {
    if($status !== false) $this->status = $status;
    if($message) $this->message = $message;
    if(!$conf = Config::$settings['error_pages'][$this->status]) $conf = Config::$settings['error_pages']['generic'];
    
    $path = $conf['path'].$conf['file'].$conf['suffix'];

    header('HTTP/1.1 '.$this->status.' '.$this->message,1,$this->status);
    header('Status '.$this->status);
    ob_end_clean();
    if(is_readable($path)) echo file_get_contents($path);
    else echo $message;
    if($trace) echo "file: ".$this->getFile()."<br />line: ".$this->getLine()."<br />trace:<pre>".$this->getTraceAsString()."</pre>";
    exit;
  }
  
}
?>