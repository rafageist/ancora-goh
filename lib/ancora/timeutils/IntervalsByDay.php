<?php

/**
 * @desc Interals by day
 * @author Rafael Rodriguez Ramirez
 */

import("lib.ancora.timeutils.HourInterals");

class IntervalsByDay{
	/**
	 * @desc Return Array of Hour Intervals from the form
	 * @desc Day1(hh:mm-hh:mm,hh:mm-hh:mm,...); Day2(hh:mm-hh:mm,hh:mm-hh:mm,...); ...
	 */
	var $stringvalue;
	var $dayintervals;

	public function __construct($intervalstring){
		$this->setFromString($intervalstring);
	}

	function updateStringValue(){
		$this->getAsString();
	}

	function getAsString(){
		$s = '';
		$i = 0;
		foreach ($this->dayintervals as $day){
			$i++;
			if ($i>1) $s = $s.';';
			$s = $s.$day->getAsString();
		}
		return $s;
	}

	function setFromString($intervals){
		$this->stringvalue = $intervals;
		$arr = explode(";",$intervals);
		$this->dayintervals = array();
		foreach ($arr as $item){
			$item = trim($item);
			if ($item!=''){
				$t = new HourIntervals($item);
				$this->dayintervals[$t->day] = $t;
			}
		}
	}

}

?>