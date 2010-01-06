<?
class CoreException extends Exception{  
  public $status = 500;
  public $message = "Application Error";
  
  public function __construct($message="", $status=false, $trace=true) {
    if($status !== false) $this->status = $status;
    if($message) $this->message = $message;
    if(!$conf = Config::$settings['error_pages'][$this->status]) $conf = Config::$settings['error_pages']['generic'];
    
    $path = $conf['path'].$conf['file'].$conf['suffix'];
    if(!headers_sent()){
      header('HTTP/1.1 '.$this->status.' '.$this->message,1,$this->status);
      header('Status '.$this->status);
    }
    ob_end_clean();
    if($trace) $stack_trace = "file: ".$this->getFile()."<br />\nline: ".$this->getLine()."<br />\ntrace:<pre>\n".$this->getTraceAsString()."\n</pre>\n\n";
    if(is_readable($path)) $message = str_ireplace("<!--TRACE-->", $stack_trace, str_replace('<!--MESSAGE-->', $message, file_get_contents($path)));
    echo $message;    
    exit;
  }
  
}
?>