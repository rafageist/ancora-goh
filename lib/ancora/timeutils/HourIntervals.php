<?php

import("lib.ancora.timeutils.HourInteral");

// 	Work with Day1(hh:mm-hh:mm,hh:mm-hh:mm,...);
class HourIntervals{

	var $stringvalue;
	var $day;
	var $intervals;

	public function __construct($intervalstring){
		$this->setFromString($intervalstring);
	}

	function updateStringValue(){
		$this->getAsString();
	}

	function getAsString(){
		$s = $this->day.'(';
		$i = 0;

		foreach ($this->intervals as $inter){
			$i++;
			if ($i>1) $s = $s.',';
			$s = $s.$inter->getAsString();
		}
		return $s.')';
	}

	function setFromString($intervalstring){
		$this->stringvalue = $intervalstring;
		$arr = explode("(", $intervalstring);
		$this->day = $arr[0];

		$arr = explode(")", $arr[1]);
		$interval = $arr[0];

		$arr = explode(",", $interval);

		foreach ($arr as $item)
		$this->intervals[] = new HourInterval($item);
	}
}

?>