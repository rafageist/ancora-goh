<?php


class PropertiesFile{

	var $properties;
	
	public function __construct($path){
		
		$this->properties = array();
		
		$f = fopen($path,"r");
		
		while (!feof($f)){
			$s = fgets($f);
			$s = trim($s);
			if ($s!="")
				if (substr($s,0,1) != "#"){
					$prop = substr($s,0,strpos($s,"="));
					$prop = trim($prop);
					$value =  trim(substr($s,strpos($s,"=") + 1));	
					
					$p = strpos($prop, "[");
					
					if ($p > 0){
						$narr = substr($prop, 0, $p - 1);
						$index = substr($prop, $p + 1, strlen($prop) - $p - 2);
						
						eval('if (!isset($this->'.$narr.')) $this->'.$narr.'= array();');
						eval('$this->'.$narr.'["'.$index.'"] = '.$value.';');
						if (!isset($this->properties[$narr])) $this->properties[$narr] = array();
						eval('$this->properties[$narr][$index] = '.$value.';');
					}
					else{
						eval('$this->'.$prop.'='.$value.';');
						eval('$this->properties[$prop] = '.$value.';');
					}
				}
		}
	}
	
	public function asString($separator = " "){
		$s = "";
		$primero = true;
		foreach ($this->properties as $key => $value){
			$s .= ($primero==false?$separator:"")."$key = $value";
			$primero = false;
		}
		return $s;
	}
}

?>