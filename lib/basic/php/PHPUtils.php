<?php
/**
	PHP Utils
*/
function getDocumentName(){
	$n = substr($_SERVER['PHP_SELF'],1);
	$arr = explode("/",$n);
	$n = $arr[count($arr)-1];
	return $n;
}
 
function getProtocol(){
    $p = $_SERVER['SERVER_PROTOCOL'];
    if (strpos(0,"HTTPS",$p)) return "https";
    return "http";
} 
 
 
function getURLSelf(){
  return getHost()."/". getDocumentName();
}

function getHost(){
  $n = substr($_SERVER['PHP_SELF'],1);
  $arr = explode("/",$n);
  $h ='';
  $c = count($arr);
  $i=0;
  foreach ($arr as $item){
    $i++;
    if ($i<$c) $h.=$item;
  }
  return getProtocol()."://".$_SERVER['SERVER_NAME']."/".$h;
  
}

function super_parse_url($url){
    $a = parse_url($url);
    $a["path"] = $url;
    
    if (!isset($a["port"])) $a["port"] = 80;
    if (!isset($a["host"])) $a["host"] = 'localhost';
    return $a;
}

/**
 * @desc Check POST send indexes
 * @param $var Index names separate by commas
 */
function checkPOST($var){
    
    $indexes = explode(",",$var);
    
    foreach ($indexes as $index)
        if (!isset($_POST[$index])) {
            return false;
        }
        
    return true;
}

/**
 * @desc Check GET send indexes
 * @param $var Index names separate by commas
 */
function checkGET($var){
    
    $indexes = explode(",",$var);
    
    foreach ($indexes as $index)
        if (!isset($_GET[$index])) {
            return false;
        }
        
    return true;
}

?>