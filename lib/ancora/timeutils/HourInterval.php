<?php

import("lib.ancora.timeutils.HourString");

class HourInterval{
	var $stringvalue;
	var $hours;

	function __construct($intervalstring='00:00:00-00:00:00'){
		$this->setFromString($intervalstring);
	}

	function updateStringValue(){
		$this->getAsString();
	}

	function getAsString(){
		$s ='';
		$i=0;
		foreach ($this->hours as $hour){
			$i++;
			if ($i>1) $s = $s.'-';
			$s = $s.$hour->getAsString();
		}
		return $s;
	}

	function setFromString($intervalstring){
		$this->stringvalue = $intervalstring;
		$arr = explode('-',$intervalstring);
		$this->hours = array();
		foreach ($arr as $item)
		$this->hours[]=new HourString($item);
	}

}
?>