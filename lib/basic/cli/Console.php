<?php

/**
 * @desc Console class utility
 * @author Rafael Rodriguez Ramirez
 */
 
 class Console{
    
    var $laststream;
    var $lastmessage;
    var $arguments;
    
    function Console(){
        if ($this->verifyCli() == true){
            $this->stdin = fopen("php://stdin", "r");
            $this->arguments = $_SERVER['argv'];
            foreach ($this->arguments as $k=>$arg){
                if (substr($arg,0,1)=='-'){
                    if (isset($this->arguments[$k+1])) 
                        $this->arguments[$arg] = $this->arguments[$k+1];
                    else
                        $this->arguments[$arg] = null;
                }
            }
        }
    }
    
    public function verifyCli(){
        return isset($_SERVER['argv']) and php_sapi_name()=='cli';
    }
    
    public function showMessage($message,$newline = true){
        echo $message;
        $this->lastmessage = $message;
        if ($newline == true) echo "\n" ;
    }
    
    public function showInfo($info, $infoicon = "[INFO]"){
        $this->showMessage($infoicon.' '.$info);
    }
    
    public function getStream($message = "", $newline = false){
       $this->showMessage($message, $newline);
       $stream = fgets($this->stdin);
       $this->laststream = $stream;
       return $stream;
    }
    
    public function getArguments(){
        return $_SERVER['argv'];
    }

    public function getArgument($number){
        return $_SERVER['argv'][$number];
    }

    public function getLastMessage(){
        return $this->lastmessage;
    }
    
    public function getLastStream(){
        return $this->laststream;
    }
 }

?>