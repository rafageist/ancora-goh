<?php
  /**
    * Class Type Date
    */
  class THour extends TimeUtilsCommon{
  
     var $h;
     var $m;
     var $s;
     
     /**
       * Constructor
       */
     public function THour($h, $m, $s){
        $this->h = $h;
        $this->m = $m;
        $this->s = $s;
     }
         
    /**
      * @desc Convert hour to number
      */ 
     public function toNumber() {
        return $this->hour_to_number($this->h,$this->m,$this->s);
     }
     
     public function asString($granudality = 3) {
     	$s = $this->h;
     	if ($granudality > 1) $s.= ':'.$this->m;
     	if ($granudality > 2) $s.= ':'.$this->s;
     	return $s;
     }
  }
?>