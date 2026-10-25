 <?php 
 class HourString{
        
        var $stringvalue;
        var $hour;
        var $minutes;
        var $seconds;
                
        /**
          * Constructor
          */
        function __construct($hourstring='00:00:00'){
            $this->setFromString($hourstring);
        }
        
        function updateStringValue(){
            $this->getAsString(true);
        }
        
        function getAsString($showseconds=true){
            $h = $this->hour.":".$this->minutes;
            if ($showseconds==true && intval($this->seconds)!=0) $h=$h.':'.$this->seconds;
            $stringvalue = $h;
            return $h;
        }
        
        function setFromString($hourstring){
            $this->stringvalue = $hourstring;
            $this->hour = '00';
            $this->minutes = '00';
            $this->seconds = '00';
            
            $arr = explode(":",$hourstring);
            
            if (isset($arr[0])) $h = $this->hour = $arr[0];
            if (isset($arr[1])) $m = $this->minutes = $arr[1];
            if (isset($arr[2])) $s = $this->seconds = $arr[2];
        }
    }
 ?>