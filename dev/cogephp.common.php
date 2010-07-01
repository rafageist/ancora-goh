<?php
 
include "../lib/import.php";
include "../lib/parsers/json/JSON.php";
include "../lib/enterprise/gendb/PostgresDatabaseImpl.php";
include "../lib/enterprise/gendb/MySQLDatabaseImpl.php";
include "cogephp.conf";

$esquemas = explode(",",$work_schema);
$mapa_type= $reverse_mapa_type;

if(!isset($_SERVER['argv']) || php_sapi_name()!=='cli')
echo "<html><head><style>body{background:white;color:blue;} img{height:16px;}</style></head><body>";


//---------------------------------//
//         FUNCIONES               //
//---------------------------------//

// Devuelve un el mismo texto $text pero con la primera letra en mayuscula
function capitalLetter($text){
	$s = '';
	$arr = explode("_",$text);
	foreach($arr as $word) $s .= strtoupper(substr($word, 0, 1)).substr($word, 1, strlen($word));
	return $s;
}

//Escribe comentarios sobre funciones y clases en $fichero con los datos pasados por parametros
function fputComentario($fichero, $nombre, $descrip, $parametros=array(), $retorna="", $ident="          ",$version='1.0'){
	fputs($fichero,"$ident/** \n");
	fputs($fichero,"$ident  * @name: $nombre \n");
	fputs($fichero,"$ident  * @desc: $descrip \n");
	foreach ($parametros as $parametro){
		fputs($fichero,"$ident  * @param: ".$parametro['n'].' > '.$parametro['d']." \n");
	}
	if ($retorna!="") fputs($fichero,"$ident  * @return: $retorna \n");
	fputs($fichero,"$ident  * @updated: ".date("d/m/y h:i:s")." \n");
	fputs($fichero,"$ident  * @version: $version \n");
	fputs($fichero,"$ident  */ \n");
}

//Devuelve el tipo de datos SQL a partir del tipo de dato de cogePHP,
//y si no exite en $mapa_type devuelve el mismo $type
function getMapaType($type){
	global $mapa_type;
	if (isset($mapa_type[$type])) return $mapa_type[$type];
	return $type;
}

//Devuelve el nombre del esquema pasado por $schema con un punto a continuaci�n
//Si $schema es una cadena vacia entonces devuelve una cadena vac�a
function depuraEsquema($schema, $concat='.'){
	if ($schema !='') $schema.=$concat;
	return $schema;
}

//Devulve TRUE si el tipo de datos implica que el dato se pase entre comillas, como
//es el caso de las cadenas de caracteres
function isEntreComillas($type){
	global $entrecomillas;
	if (isset($entrecomillas[$type])) return $entrecomillas[$type];
	return true;
}

//Devuelve TRUE si los datos de un campo de una tabla es trabajaao entre comillas
function llevaComillas($modelo,$esquema,$tabla,$campo){
	foreach($modelo->data->tables as $table){
		if ($table->schema==$esquema && $table->name == $tabla){
			foreach($table->fields as $fd){
				if ($fd->name==$campo)
				return isEntreComillas($fd->type);
			}
		}
	}
	return null;
}

//Imprime una cadena con un salto de linea
function prints($cadena){
	if(!isset($_SERVER['argv']) || php_sapi_name()!=='cli'){
		$cadena = str_replace("[INFO]","<img src='info.png'>",$cadena);
		$cadena = str_replace("[OK]","<img src='ok.png'>",$cadena);
		$cadena = str_replace("[FAIL]","<img src='fail.png'>",$cadena);
		echo $cadena."</br>";
	}
	else {
		$cadena = str_replace("<i>","",$cadena);
		$cadena = str_replace("</i>","",$cadena);
		echo $cadena."\n";
	}
}

function esta_en_arreglo($arr, $element, $schema = ""){
	foreach ($arr as $key=>$val) {
        if ($element == $val) return true;
        if ($val == '{'.$schema.'}') return true;
    }
	return false;
}

//Arreglar el nombre de una funcion teniendo en cuenta las palabras claves de PHP
function fixFunctionName($n, $prefix = "go"){
	global $phpkeywords;
	$nn = capitalLetter($n);

	if (esta_en_arreglo($phpkeywords,$n))
	$nn = $prefix.$nn;
	return $nn;
}

//Saber si dos nombres de esquemas son iguales
function SchemasIguales($s1,$s2){
	if ($s1==$s2) return true;
	if ($s1=='public' && $s2=='') return true;
	if ($s1=='' && $s2=='public') return true;
	return false;
}

//prepara paquetes

function preparaPaquetes(){

	global $packages;
    global $relative_path;
    
	prints("[INFO] Creando paquetes");
	foreach ($packages as $key => $clases){
		prints("[INFO] Creando paquete <i>$key</i>");
		$arr = explode(".",$key);
		$f = $relative_path;
		foreach ($arr as $ff){
			$f .= "$ff/";
			if (!file_exists("$f")) mkdir("$f");
		}
	}
}

function dame_carpeta_de($classname, $schema = ""){

	global $packages;
	global $default_package;

	foreach ($packages as $key => $clases){
		$arr = explode(",",$clases);
		foreach ($arr as $k=>$value) $arr[$k] = trim($value);
		if (esta_en_arreglo($arr, $classname, $schema) == true)
		return str_replace(".","/",$key);

	}
	return $default_package;
}


function capitalSpacing($text){
	$s = '';
	$arr = explode("_",$text);
	foreach($arr as $word) $s .= strtoupper(substr($word, 0, 1)).substr($word, 1, strlen($word))." ";
	return trim($s);
}


 
function getDescriptor($table, $field){
	global $foreign_key_descriptor;
	if (isset($foreign_key_descriptor[$table])){
		if (isset($foreign_key_descriptor[$table][$field])){
			return $foreign_key_descriptor[$table][$field];
		}
	}
	return $field;
}

function getTitulo($fname){
	global $titulos;
	global $ortografia;
	$n = $fname;
	if (isset($titulos[$fname])) $n = $titulos[$fname]; else $n = capitalSpacing($fname);
	foreach ($ortografia as $key=>$value) $n = str_replace($key,$value,$n);
	return $n;
}



function dame_paquete_de($classname){

	global $packages;
	global $default_package;

	foreach ($packages as $key => $clases){
		$arr = explode(",",$clases);
		foreach ($arr as $k=>$value) $arr[$k] = trim($value);
		if (esta_en_arreglo($arr,$classname) == true)
		return $key;
			
	}
	return $default_package;
}

function searchInPublicSchema($tab){
	global $modelo;
	foreach ($modelo->data->tables as $tabla){
		if ($tabla->schema =='' || $tabla->schema='public'){
			if ($tab == $tabla->name)
			return true;
		}
	}
	return false;
}

function getClassNameOf($schema,$table){

	global $schema_name_to_sufix,  $ignore_schema_name_equal_class_name;
	global $ignore_prefix_class_name;
	$with_schema_name = true;

	if ($schema == $table && $ignore_schema_name_equal_class_name == true && searchInPublicSchema($table)==false)
	$with_schema_name =false;

	if (isset($ignore_prefix_class_name[$schema]))
	$classn = capitalLetter($table);
	else{
		if ($schema_name_to_sufix == false)
		$classn = ($with_schema_name ==true?capitalLetter($schema):'').capitalLetter($table);
		else
		$classn = capitalLetter($table).($with_schema_name ==true?capitalLetter($schema):'');
	}
	return $classn;
}


function cargaModelo($modelfilename){

	global $foldersrc;

	if (!file_exists($modelfilename)) $modelfilename = strtolower($modelfilename);

	//Abriendo el archivo que contiene el modelo. El nombre $modelfilename se
	//especifica en el archivo de configuracion cogephp.conf
	echo "[INFO] Abriendo el archivo del modelo...'".$modelfilename."   ";
	 
	if (!file_exists($modelfilename)) $modelfilename = strtolower($modelfilename);
	 
	$fichero = fopen($foldersrc."/$modelfilename","r");
	 
	if ($fichero!=false){
		prints("[OK]");
		prints("[INFO] Cargando modelo '".$modelfilename."' ...");

		$json = new Services_JSON();
		$jsoncode = "";
		 
		while (!(feof($fichero))) $jsoncode = $jsoncode.fgets($fichero);
		
		$modelo = $json->decode($jsoncode);
		
		$modelo->data->tables = array();
		 
		foreach ($modelo->data->tabledefs as $td){
			 
			echo "      Cargando submodelo tabla $td ... ";
			 
			$ftd = fopen ($foldersrc."/$td","r");
			 
			if (!$ftd) echo "[fallo] \n";
			else {
				$jsoncode='';
				while (!(feof($ftd))) {
					$s = fgets($ftd);
					$jsoncode = $jsoncode.$s;
				}
				$jtd = $json->decode($jsoncode);
				$modelo->data->tables[] = $jtd;
				echo "[OK] \n";
				 
				fclose ($ftd);
			}
		}
		 
		$modelo->data->views = array();
		 
		if (is_array($modelo->data->viewdefs))
		foreach ($modelo->data->viewdefs as $td){
			 
			echo "       Cargando submodelo vista $td ... ";
			 
			$ftd = fopen ($foldersrc."/$td","r");
			 
			if (!$ftd) echo "[fallo] \n";
			else {
				$jsoncode='';
				while (!(feof($ftd))) {
					$s = fgets($ftd);
					$jsoncode = $jsoncode.$s;
				}
				$jtd = $json->decode($jsoncode);
				if (!isset($modelo->data->views[$jtd->schema])) $modelo->data->views[$jtd->schema]=array();
				$modelo->data->views[$jtd->schema][] = $jtd;
				echo "[OK] \n";
				 
				fclose ($ftd);
			}
			 
		}
		 
		$modelo->data->functions = array();

		foreach ($modelo->data->functiondefs as $td){
			 
			echo "       Cargando submodelo funcion $td ... ";
			 
			$ftd = fopen ($foldersrc."/$td","r");
			 
			if (!$ftd) echo "[fallo] \n";
			else {
				$jsoncode='';
				while (!(feof($ftd))) {
					$s = fgets($ftd);
					$jsoncode = $jsoncode.$s;
				}
				$jtd = $json->decode($jsoncode);
				if (!isset($modelo->data->functions[$jtd->schema])) $modelo->data->functions[$jtd->schema]=array();
				$modelo->data->functions[$jtd->schema][] = $jtd;
				echo "[OK] \n";
				 
				fclose ($ftd);
			}
		}
		 
		$modelo->data->services = array();
		
		if (isset($modelo->data->services_defs))
		 if (is_array($modelo->data->services_defs))
		  foreach ($modelo->data->services_defs as $td){
			 
			echo "       Cargando submodelo servicio $td ... ";
			 
			$ftd = fopen ($foldersrc."/$td","r");
			 
			if (!$ftd) echo "[fallo] \n";
			else {
				$jsoncode='';
				while (!(feof($ftd))) {
					$s = fgets($ftd);
					$jsoncode = $jsoncode.$s;
				}
				$jtd = $json->decode($jsoncode);
				$modelo->data->services[] = $jtd;
				echo "[OK] \n";
				 
				fclose ($ftd);
			}
		}
	}
	return $modelo;
}

function ValidSchema($schema){
	global $esquemas;
	foreach($esquemas as $esquema){
		if ($schema==$esquema) return true;
	}
	return false;
}

function getCrudalTitleOf($n){
    global $ortografia;
    $s = '';
    $arr = explode("_",$n);
    $primero = true;
    foreach ($arr as $p){
        if ($primero == true) {$p = capitalLetter($p); $primero =false;}
        $s .= $p." ";
    }
    foreach ($ortografia as $key=>$value) $s = str_replace($key,$value,$s);  
    return trim($s);
}

function getTitleOf($n){
    $s = getCrudalTitleOf($n);
    return '<label class = "title-of title-of-'.str_replace("_","-",$n).'">'.trim($s).'</label>';
}

?>