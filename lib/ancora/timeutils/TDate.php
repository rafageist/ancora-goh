<?php
  /**
    * Class Type Date
    */
  class TDate extends TimeUtilsCommon{
     
     var $d;
     var $m;
     var $a;
     
     /**
       * Constructor
       */
     public function TDate($d, $m, $a){
        $this->d = $d;
        $this->m = $m;
        $this->a = $a;
     }
     
     /**
       * @desc Convert to number
       */
     public function toNumber(){
        return $this->date_to_number($this->d,$this->m,$this->a);
	 }
	 
	 public function asString(){
	 	return ($this->d<10?'0':'').$this->d.'/'.($this->m<10?'0':'').$this->m.'/'.$this->a;
	 }
  }
?>