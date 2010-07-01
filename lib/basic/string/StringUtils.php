<?php

/**
 * String's utils
 */

/**
 * @name        getReplica
 * @description Construct a id replica
 * @return      Random String
 */

function getReplica($prefix, $min=50, $max=100, $characters="abcdefghijklmnopqrstuvwxyz1234567890"){

	$c = $characters;
	$count = rand($min,$max);
	$s ='';
	for($i=0;$i<$count; $i++) $s = $s.substr($c, rand(0,strlen($c)-1), 1);
	$momento=str_replace("/","",date("d/m/y h:i:s"));
	$momento=str_replace(" ","_",$momento);
	$momento=str_replace(":","",$momento);
	return $prefix.$momento.time().$s;
}

/**
 * @name        getStringParams
 * @description Crea una cadena de parametros  partir de un arreglo
 * @params      $arr - arreglo de parametros de la forma array("nombre_parametro"=>"valor",....)
 * @return      Cadena de parametros de la forma "nombre_parametro=valor nombre_parametro=valor ..."
 */

function getStringParams($arr){
	$result = "";

	foreach ($arr as $key => $value)
	$result = $result.$key."=".$value." ";

	return $result;
}

/**
 * @name        removeTildes
 * @description Remove latin tildes from string
 * @params      $s - String
 * @return      String
 */

function removeTildes($s){
	$s=str_replace('�','a',$s);
	$s=str_replace('�','e',$s);
	$s=str_replace('�','i',$s);
	$s=str_replace('�','o',$s);
	$s=str_replace('�','u',$s);
	$s=str_replace('�','A',$s);
	$s=str_replace('�','E',$s);
	$s=str_replace('�','I',$s);
	$s=str_replace('�','O',$s);
	$s=str_replace('�','U',$s);  
	$s=str_replace('�','n',$s);
	$s=str_replace('�','N',$s);
	$s=str_replace('�','u',$s);
	$s=str_replace('�','U',$s);
	return $s;
}

function CodificationRepair($s){
	$s=str_replace('�','&iacute;',$s);
	$s=str_replace('á','&aacute;',$s);
	$s=str_replace('Á','&Aacute;',$s);
	$s=str_replace('�','&aacute;',$s);

	$s=str_replace('�','&aacute;',$s);
	$s=str_replace('�','&eacute;',$s);
	$s=str_replace('�','&iacute;',$s);
	$s=str_replace('�','&oacute;',$s);
	$s=str_replace('�','&uacute;',$s);

	$s=str_replace('�','&Aacute;',$s);
	$s=str_replace('�','&Iacute;',$s);
	$s=str_replace('�','&Eacute;',$s);
	$s=str_replace('�','&Oacute;',$s);
	$s=str_replace('�','&Uacute;',$s);

	$s=str_replace('�','&Nacute;',$s);
	$s=str_replace('�','&ntilde;',$s);
	$s=str_replace('�','&uuml;',$s);
	$s=str_replace('�','&Uuml;',$s);

	$news = $s;

	return removeTildes($news);
}

?>