<?php
/*
 ____________________________________
 _______/                                   /
 ______/ - ** cogePHP **                   /
 _____/ - C-oge O-tro GE-nerador para PHP /
 ____/ - version 1.0                     /
 ___/ - por Rafael Rodriguez Ramirez    /
 __/___________________________________/

 Generador de codigo PHP

 Este script genera el

 - Script SQL
 - Capa de acceso a datos
 - Mapeo objeto-relacional

 desde un modelo en JSON, con una especificacion requerida

 */

include "cogephp.common.php";

// preparando...

    preparaPaquetes();

// cargando el modelo ...

    $modelo = cargaModelo($modelfilename);

// Generando los scripts SQL ...

	prints("[INFO] Creando los scripts SQL...");

	$file_sqlcreator = fopen($classpath."/".$fn_sqlcreator,"w");
	$file_sqldropper = fopen($classpath."/".$fn_sqldropper,"w");

	foreach ($modelo->data->tables as $tabla){

		fputs($file_sqldropper,"DROP TABLE ".depuraEsquema($tabla->schema).$modelo->data->prefix.$tabla->name." CASCADE; \n");

		prints("[INFO] Creando SCRIPT sql...tabla ".depuraEsquema($tabla->schema).$tabla->name);
		fputs($file_sqlcreator,"CREATE TABLE ".depuraEsquema($tabla->schema).$modelo->data->prefix.$tabla->name."(");

		if (count($tabla->primary_key)>0) {
			$i=0;
			fputs($file_sqlcreator,"PRIMARY KEY (");
			foreach ($tabla->primary_key as $llave){
				$i++;
				if ($i>1) fputs($file_sqlcreator,",");
				fputs($file_sqlcreator,$llave);
			}
			fputs($file_sqlcreator,"),");
		}

		$i = 0;

		foreach ($tabla->fields as $campo){
			$i++;
			if ($i > 1) fputs($file_sqlcreator,", ");
			fputs($file_sqlcreator, $campo->name." ".getMapaType($campo->type));
			if (getMapaType($campo->type)=='varchar') fputs($file_sqlcreator,"(".$campo->long.")");
			if ($campo->allownull == false) fputs($file_sqlcreator," NOT NULL ");
			if ($campo->unique == true) fputs($file_sqlcreator," UNIQUE ");
		}

		fputs($file_sqlcreator,");\n");
	}

	prints("         Generando SQL sobre las llaves foraneas ");
	foreach ($modelo->data->tables as $tabla){
		prints("         ....".depuraEsquema($tabla->schema).$tabla->name);
		foreach ($tabla->foreign_keys as $foranea){
			fputs($file_sqlcreator," ALTER TABLE ".depuraEsquema($tabla->schema).$modelo->data->prefix.$tabla->name." ADD FOREIGN KEY (");

			$i=0;
			foreach ($foranea->fields as $campo){
				$i++;
				if ($i>1) fputs($file_sqlcreator,",");
				fputs($file_sqlcreator,$campo->localfield);
			}
			fputs($file_sqlcreator,") REFERENCES ".depuraEsquema($foranea->foreign_schema).$modelo->data->prefix.$foranea->foreign_table."(");

			$i=0;
			foreach ($foranea->fields as $campo){
				$i++;
				if ($i>1) fputs($file_sqlcreator,",");
				fputs($file_sqlcreator,$campo->foreign_key);
			}
			fputs($file_sqlcreator,") ");

			if ($foranea->on_delete_cascade) fputs($file_sqlcreator," ON DELETE CASCADE ");
			if ($foranea->on_update_cascade) fputs($file_sqlcreator," ON UPDATE CASCADE ");
			fputs($file_sqlcreator,"; \n");
		}
	}

	prints("[INFO] Creando SCRIPT php...");

	/*
    $file_control = fopen($classpath."/".$fn_control,"w");
*/
	/**
	 Encabezado fichero control
	 */
/*
	fputs($file_control,"<?php \n");
	fputs($file_control,"\n");
	fputComentario($file_control,$classcontrolname,'Part Control of MVC Pattern',array(),'','   ');

	fputs($file_control,"    class ".$classcontrolname."{ \n \n");
	fputs($file_control,'          var $model;'." \n \n");

	fputComentario($file_control,$classcontrolname,'Constructor',array());

	fputs($file_control,'          public function '.$classcontrolname.'($host="'.$dbhost.'", $port='.$dbport.', $dbname="'.$dbname.'", $user="'.$dbuser.'", $password="'.$dbpass.'",$connectnow=true){'." \n");
	fputs($file_control,'                $this->model = new '.$classmodelname.'($host,$port,$dbname,$user,$password,$connectnow);'." \n");
	fputs($file_control,'          }'." \n");
*/
	$file_model = fopen($relative_path.str_replace("..",".",dame_carpeta_de("PartModel", "")."/PartModel.php"),"w");
	

	fputs($file_model,"<?php \n");
	fputComentario($file_model,'PartModel','Father of other class model',array(),"","");

	fputs($file_model,"   class PartModel { \n \n");
	fputs($file_model,'          var $db;'." \n \n");
	fputs($file_model,'          public function PartModel($db){'." \n");
	fputs($file_model,'                $this->db = $db; '." \n");
	fputs($file_model,'          }'." \n");
	fputs($file_model,
        '          /**'."\n".
        '            * @name getAnything'."\n".
        '            * @descrip Get anything SQL query'."\n".
        '            */'."\n".
        "\n".   
        '             public function getAnything($sql,$asobjects=false){'."\n".
        '               if ($asobjects==true) return $this->db->getAsObjects($sql);'."\n".
        '               return $this->db->getAsArray($sql);'."\n".             
        "          } \n".
        "\n".
        '          /**'."\n".
        '            * @name getWHEREPartOfSearchFrase'."\n".
        '            * @descrip Construct WHERE part of SQL query from phrase ...'."\n".
        '            */'."\n".
        "\n");

	fputs($file_model,
'        function getWHEREPartOfSearchFrase($frase,$fields){
           $arr = explode(",",$fields); $words=explode(\' \',trim($frase));
            $parteand =\' TRUE \';
            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)==\'"\' || substr($word,strlen($word),1)==\'"\') {
                     $word=str_replace(\'"\',\'\',$word);
                     $parteand=$parteand.\' AND \'.$field." LIKE \'%".$word."%\' ";
                }
             }
             $parteor =\' AND (TRUE\';

            foreach($arr as $field) foreach($words as $word){
                if (!(substr($word,0,1)==\'"\' || substr($word,strlen($word),1)==\'"\')) {
                     $parteor=$parteor.\' OR \'.$field." LIKE \'%".$word."%\' ";
                }
            }

            $parteor = $parteor.\')\';
            return $parteand.$parteor;
        }
');
	fputs($file_model,"\n");
	fputs($file_model,
        '          /**'."\n".
        '            * @name getAllWithPhraseIn'."\n".
        '            * @descrip Get all records from table that cotains this phrase...'."\n".
        '            */'."\n".
        "\n".
        '          public function getAllWithPhraseIn($table,$fields,$phrase,$extrawhere="true", $showfields="*",$asobjects=false){'."\n".
        '              return $this-getAnything("SELECT $showfields FROM $table WHERE (".$this->getWHEREPartOfSearchFrase($fields).") AND ($extrawhere);");'."\n".
        '          }'."\n");


	fputs($file_model,"}\n ?> \n");
	fclose($file_model);

	foreach ($modelo->data->views as $key => $tablas){

		$file_model = fopen($relative_path.dame_carpeta_de(capitalLetter($key).'DatabaseViews',"vistas")."/".capitalLetter($key).'DatabaseViews.php',"w");

		fputs($file_model,"<?php \n");

		fputs($file_model,'   class '.capitalLetter($key)."DatabaseViews extends PartModel { \n \n");

		fputComentario($file_model,capitalLetter($key)."DatabaseViews",'Constructor',array(),'','       ');

		fputs($file_model,'          public function '.capitalLetter($key)."DatabaseViews".'($db){'." \n");
		fputs($file_model,'                $this->db = $db; '."\n");
		fputs($file_model,'          }'." \n");
			
		foreach ($tablas as $tabla){
			fputComentario($file_model, capitalLetter($key)."DatabaseViews", 'Constructor', array(), '', '       ');
			fputs($file_model,"           public function get".capitalLetter($tabla->name)."(".'$asobjects = false'."){ \n");
			fputs($file_model,'                return $this->getAnything("'."SELECT * FROM  ".$tabla->name.';", $asobjects);'."\n");
			fputs($file_model,"           } \n");
		}

		fputs($file_model,"    } \n");
		fputs($file_model,"?>");
		fclose($file_model);
	}

	

	foreach ($modelo->data->tables as $tabla){
		$classname = getClassNameOf($tabla->schema, $tabla->name);
		$carpeta = dame_carpeta_de($classname, $tabla->schema);
	     
		$file_model = fopen($relative_path."$carpeta/{$classname}Model.php","w");

		fputs($file_model,"<?php \n");

		fputs($file_model,'   class '.$classname."Model extends PartModel { \n \n");

		fputs($file_model,'          public function '.$classname.'Model($db){'." \n");
		fputs($file_model,'                $this->db = $db;'." \n");
		fputs($file_model,'          }'." \n");
			
		$metodosufijo = capitalLetter($tabla->name); // depuraEsquema($tabla->schema,"_")
		$tnombre = depuraEsquema($tabla->schema).$modelo->data->prefix.$tabla->name;
		$llaves = ""; $i = 0; foreach ($tabla->primary_key as $llave){$i++; if ($i>1) $llaves = $llaves.","; $llaves = $llaves."$".$llave;}
		$camposphp = ""; $i = 0; foreach ($tabla->fields as $campo) if ($campo->type!='replica'){ $i++; if ($i>1) $camposphp = $camposphp.","; $camposphp = $camposphp."$".$campo->name;}
		$campossql = ""; $i = 0; foreach ($tabla->fields as $campo){ $i++; if ($i > 1) $campossql = $campossql.","; $campossql = $campossql.$campo->name; }

		prints("[INFO] Creando SCRIPT php...".$tnombre);
			
		/**
		 * @section MODELO
		 */

		fputComentario($file_model,$tnombre,'Work with table "'.depuraEsquema($tabla->schema,"_").$tabla->name.'"',array(),'','         ');
			
		//obtener la cantidad
		fputComentario($file_model,"length",'Get count of "'.$tnombre.'"',array());
		fputs($file_model,"          public function length(".'$fieldcount="id"'."){ \n");
		fputs($file_model,'              $r = $this->getAnything("'."SELECT ".'count($fieldcount)'." as c FROM $tnombre; \"); \n");
		fputs($file_model,'              if (isset($r[0])) return $r[0]["c"];'."\n");
		fputs($file_model,"              return 0; \n");
		fputs($file_model,"         }\n");
			
        $camposbool = array();
        $cantcamposbool = 0;
        foreach($tabla->fields as $campo){
           if ($campo->type == 'bool' || $campo->type == 'boolean'){
                $cantcamposbool++;
                $camposbool[] = $campo->name;
           }
        }
        
        if ($cantcamposbool >0 ){
            
            fputs($file_model,'          public function repairBoolean($obj, $asobject = true){'."\n");
            
            fputs($file_model,'             if ($asobject == true){'."\n");
            
            foreach ($camposbool as $campobool){
            fputs($file_model,'                  if (isset($obj->'.$campobool.')) {if ($obj->'.$campobool.' == "f") $obj->'.$campobool.' = false; else $obj->'.$campobool.' = true; }'."\n");
            }
            
            fputs($file_model,'             } else {'."\n");
            
            foreach ($camposbool as $campobool){
            fputs($file_model,'                  if (isset($obj["'.$campobool.'"])) {if ($obj["'.$campobool.'"] == "f") $obj["'.$campobool.'"] = false; else $obj["'.$campobool.'"] = true; }'."\n");
            }
            fputs($file_model,'             }'."\n");
            
            fputs($file_model,'             return $obj;'."\n");
            
            fputs($file_model,'          }'."\n");
        }
        
		//insertar

		fputComentario($file_model,"add",'Add "'.$tnombre.'"',array());
			
		fputs($file_model,"          public function add($camposphp){ \n");
		fputs($file_model,'             $replicas = array();'."\n");
		foreach ($tabla->fields as $campo){
			if ($campo->type=='replica') {
				fputs($file_model,"             $".$campo->name.' = getReplica("'.$tnombre.'");'."\n");
				fputs($file_model,'             $replicas["'.$campo->name.'"] = $'.$campo->name.';'."\n");
			}
		}

		foreach ($tabla->fields as $campo){
			if ($campo->type=='bool' || $campo->type=='boolean') {
				fputs($file_model, '               if ($'.$campo->name.' == true || $'.$campo->name.' == 1 || $'.$campo->name.' == "t") $'.$campo->name.' = "true"; '."\n");
				fputs($file_model, '               else if ($'.$campo->name.'== true || $'.$campo->name.' == 0 || $'.$campo->name.' == "f") $'.$campo->name.' = "false"; '."\n");
			}
		}
			

		fputs($file_model,'             $r = $this->getAnything("'."INSERT INTO $tnombre ($campossql) VALUES (");

		$i=0;
		$entrecom = array();
			
		foreach ($tabla->fields as $campo){

			$i++;
			if ($i>1) fputs($file_model,",");
			$entrecom[$campo->name] = isEntreComillas($campo->type);
			if ($campo->type=='replica')
			fputs($file_model,'\'$'.$campo->name.'\'');
			else{
				if ($campo->type=='md5')
				fputs($file_model,"'".'".md5($'.$campo->name.')."'."'");
				else {
					if ($entrecom[$campo->name])
					fputs($file_model,'".('.$campo->name."==null?'null':".'"\'".$'.$campo->name.'."\'")."');
					else
					fputs($file_model,'".('.$campo->name."==null?'null':$".$campo->name.')."');
					//fputs($file_model,"$".$campo->name);
				}
			}
		}

		fputs($file_model,');"); '."\n".'             return $replicas; '."\n".'            }'."\n");

		//Eliminar uno

		if (count($tabla->primary_key)>0) {
			fputComentario($file_model,"remove",'Remove "'.$tnombre.'"',array());
			fputs($file_model,"          public function remove($llaves){ \n");
			fputs($file_model,'                  return $this->getAnything("'."DELETE FROM $tnombre WHERE ");
			$i=0;
			foreach ($tabla->primary_key as $llave){
				$i++;
				if ($i>1) fputs($file_model," AND ");
				fputs($file_model," ".$llave." = ");
				if ($entrecom[$llave])
				fputs($file_model,"'$".$llave."' ");
				else
				fputs($file_model,"$".$llave." ");
			}
			fputs($file_model,';");'."\n".'            }'."\n");
		}
			
		//Eliminar varios

		fputComentario($file_model,"remove","Remove '$tnombre' by ...",array());
		fputs($file_model,
         "          public function removeBy".'($where, $asobjects = false){'."\n".
         '                 return $this->getAnything("DELETE FROM '.$tnombre.' WHERE $where;",$asobjects);'."\n".
         "          } \n");

		//Limpiar
		fputComentario($file_model,"clean","Clean '$tnombre'",array());
		fputs($file_model,
         "          public function clean".'($asobjects = false){ '."\n".
         '               return $this->getAnything("DELETE FROM '.$tnombre.'", $asobjects);'.
         "          } \n");
			
		//Actualizar a todos
		fputComentario($file_model,"set","Set '$tnombre'",array());
		fputs($file_model,
         "          public function set".'($set, $asobjects = false){'."\n".
         '               return $this->getAnything("UPDATE '.$tnombre.' SET $set;",$asobjects);'."\n".
         "          } \n");

		//Actualizar segun...
		fputComentario($file_model,"set","Set '$tnombre' by ...",array());
		fputs($file_model,
         "          public function setBy".'($where, $set, $asobjects = false){'."\n".
         '               return $this->getAnything("UPDATE '.$tnombre.' SET $set WHERE $where;");'."\n".
         "          } \n");
			
		//Obtener entidad
		if (count($tabla->primary_key)>0) {
			fputComentario($file_model,"get",'Get specified "'.$tnombre.'"',array());
			fputs($file_model,"          public function get($llaves){ \n");
			fputs($file_model,'                  $r = $this->getAnything("'."SELECT * FROM $tnombre WHERE ");
			$i=0;
			foreach ($tabla->primary_key as $llave){
				$i++;
				if ($i>1) fputs($file_model," AND ");
				fputs($file_model," ".$llave." = ");
				if ($entrecom[$llave])
				fputs($file_model,"'$".$llave."' ");
				else
				fputs($file_model,"$".$llave." ");
			}
			fputs($file_model,';",true);'."\n");
			if ($cantcamposbool > 0)
                fputs($file_model,'                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;'."\n".'            }'."\n");
            else
                fputs($file_model,'                   if (isset($r[0])) return $r[0]; else return false;'."\n".'            }'."\n");
		}
			
		//Obtener todos
		fputComentario($file_model,"getAll","Get all '$tnombre'",array());
        
        $code_repairBool = 'if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);';
        
        if ($cantcamposbool > 0){
            fputs($file_model,
             "          public function getAll".'($fields = "*", $asobjects = false){'."\n".
             '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.';",$asobjects);'."\n".
             '               '.$code_repairBool."\n".
             '               return $r;'."\n".
             "          } \n");
             
		} else {
            fputs($file_model,
             "          public function getAll".'($fields = "*", $asobjects = false){'."\n".
             '               return $this->getAnything("SELECT $fields FROM '.$tnombre.';",$asobjects);'."\n".
             "          } \n");
        }
        
		//Obtener segun...
		fputComentario($file_model,"getAllBy","Get '$tnombre' by ...",array());
        if ($cantcamposbool > 0){
            fputs($file_model,
             '          public function getAllBy($where, $fields = "*", $asobjects = false){'."\n".
             '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' WHERE $where;",$asobjects);'."\n".
             '               '.$code_repairBool."\n".
             '               return $r;'."\n".
             "          } \n");
        } else {
            fputs($file_model,
             '          public function getAllBy($where, $fields = "*", $asobjects = false){'."\n".
             '               return $this->getAnything("SELECT $fields FROM '.$tnombre.' WHERE $where;",$asobjects);'."\n".
             "          } \n");
        }
        
		//Obtener todos limitados...
		fputComentario($file_model,"getLimit","Limited get '$tnombre'",array());
        if ($cantcamposbool > 0){
            fputs($file_model,
             "          public function getLimit".'($limit, $offset, $fields = "*", $asobjects = false){'."\n".
             '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' LIMIT $limit OFFSET $offset;",$asobjects);'."\n".
             '               '.$code_repairBool."\n".
             '               return $r;'."\n".
             "          } \n");
        } else {
            fputs($file_model,
             "          public function getLimit".'($limit, $offset, $fields = "*", $asobjects = false){'."\n".
             '               return $this->getAnything("SELECT $fields FROM '.$tnombre.' LIMIT $limit OFFSET $offset;",$asobjects);'."\n".
             "          } \n");        
        }
        
		//Obtener todos limitados y segun...
		fputComentario($file_model,"getLimitBy","Limited get '$tnombre' by ...",array());
        if ($cantcamposbool > 0){
            fputs($file_model,
             "          public function getLimitBy".'($limit, $offset, $where, $fields = "*", $asobjects = false){'."\n".
             '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);'."\n".
             '               '.$code_repairBool."\n".
             '               return $r;'."\n".
             "          } \n");
        } else {
            fputs($file_model,
             "          public function getLimitBy".'($limit, $offset, $where, $fields = "*", $asobjects = false){'."\n".
             '               return $this->getAnything("SELECT $fields FROM '.$tnombre.' WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);'."\n".
             "          } \n");        
        }
        
		//get joins
		$funcs = array();
		foreach($tabla->foreign_keys as $foranea){

			$tforanea = depuraEsquema($foranea->foreign_schema).$modelo->data->prefix.$foranea->foreign_table;

			$schm = capitalLetter($foranea->foreign_schema);
			if ($schm == "Public") $schm = "";

			$fsufijo = capitalLetter($schm).$modelo->data->prefix.capitalLetter($foranea->foreign_table);

			$fname = "getJoinWith$fsufijo";
			while (isset($funcs[$fname])) $fname .= "A";
			$funcs[$fname] = true;

			fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.'"',array());

			fputs($file_model,"          public function $fname".'($fields = "*", $asobjects = false){'."\n".
                                  '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' INNER JOIN '.$tforanea.' ON');
			$i=0;
			foreach ($foranea->fields as $campo) {
				$i++;
				if ($i>1) fputs($file_model,' AND ');
				fputs($file_model,' '.$tnombre.'.'.$campo->localfield.' = '.$tforanea.'.'.$campo->foreign_key.' ');
			}
			fputs($file_model,'");'."\n");
           
            if ($cantcamposbool > 0)
                fputs($file_model,'               '.$code_repairBool."\n");
            
            fputs($file_model,'             return $r; '."\n");

            fputs($file_model,"          } \n");
		}

		// get joins with where
		$funcs = array();
		foreach($tabla->foreign_keys as $foranea){

			$tforanea = depuraEsquema($foranea->foreign_schema).$modelo->data->prefix.$foranea->foreign_table;

			$schm = capitalLetter($foranea->foreign_schema);
			if ($schm == "Public") $schm = "";

			$fsufijo = capitalLetter($schm).$modelo->data->prefix.capitalLetter($foranea->foreign_table);

			fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.' by ..."',array());

			$fname = "getJoinWith$fsufijo";
			while (isset($funcs[$fname])) $fname .= "A";
			$funcs[$fname] = true;

			fputs($file_model,"          public function $fname".'By($where, $fields = "*", $asobjects = false){'."\n".
                                  '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' INNER JOIN '.$tforanea.' ON');
			$i=0;
			foreach ($foranea->fields as $campo) {
				$i++;
				if ($i>1) fputs($file_model,' AND ');
				fputs($file_model,' '.$tnombre.'.'.$campo->localfield.' = '.$tforanea.'.'.$campo->foreign_key.' ');
			}
			fputs($file_model,' WHERE $where");'."\n");
            
            if ($cantcamposbool > 0)
                fputs($file_model,'               '.$code_repairBool."\n");
            
            fputs($file_model,'             return $r; '."\n");
            fputs($file_model,"          } \n");
		}

		$funcs = array();
		// get joins with where and phrases
		foreach($tabla->foreign_keys as $foranea){

			$tforanea = depuraEsquema($foranea->foreign_schema).$modelo->data->prefix.$foranea->foreign_table;

			$schm = capitalLetter($foranea->foreign_schema);
			if ($schm == "Public") $schm = "";

			$fsufijo = capitalLetter($schm).$modelo->data->prefix.capitalLetter($foranea->foreign_table);

			fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.', by phrase ..."',array());


			$fname = "getJoinWith$fsufijo";
			while (isset($funcs[$fname])) $fname .= "A";
			$funcs[$fname] = true;

			fputs($file_model,
             "          public function $fname".'ByPhrase($phrase, $where="true", $fields="*", $asobjects = false){'."\n".
             '               if ($fields == "*") {'."\n".
             '                    $fieldstabla = explode($this->fieldnames["'.$tnombre.'"]); '."\n".
             '                    $fieldsforaneas = explode($this->fieldnames["'.$foranea->foreign_table.'"]); '."\n".
             '                    $ff = "";'."\n".
             '                    $i = 0;'."\n".
             '                    foreach ($fieldstabla as $f) {'."\n".
             '                        $i++; '."\n".
             '                        if ($i>1) $ff = $ff.",";'."\n".
             '                        $ff = $ff.$f;'."\n".
             '                    }'."\n".
             '                    foreach ($fieldsforaneas as $f) {'."\n".
             '                        $i++; '."\n".
             '                        if ($i>1) $ff = $ff.",";'."\n".
             '                        $ff = $ff.$f;'."\n".
             '                    }'."\n".   
             '                    $fields=$ff;'."\n".                
             '               }'."\n".   
             '               $r = $this->getAnything("SELECT $fields FROM '.$tnombre.' INNER JOIN '.$tforanea.' ON');
			$i=0;
			foreach ($foranea->fields as $campo) {
				$i++;
				if ($i>1) fputs($file_model,' AND ');
				fputs($file_model,' '.$tnombre.'.'.$campo->localfield.' = '.$modelo->data->prefix.$foranea->foreign_table.'.'.$campo->foreign_key.' ');
			}
			fputs($file_model,' WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");'."\n");

            if ($cantcamposbool > 0)
                fputs($file_model,'               '.$code_repairBool."\n");

            fputs($file_model,'             return $r; '."\n");
            fputs($file_model,'          }'."\n");
		}

		fputs($file_model,"    } \n");
		fputs($file_model,"?>");

		/////////////////////////////
		/* mapeo objeto relacional*/
			

		//COLLECTION

		$collectionname = $classname."Collection";

		$ficheroclass = fopen($relative_path."/$carpeta/".$collectionname.".php","w");
		fputs($ficheroclass, "<?php \n");

		fputs($ficheroclass, "   class $collectionname{ \n");

		fputs($ficheroclass, '       var $data; '." \n");
		fputs($ficheroclass, '       var $collection; '." \n");
		fputs($ficheroclass, '       var $last_offest; '." \n");
		fputs($ficheroclass, '       var $last_limit; '." \n");

		fputComentario($ficheroclass,$collectionname,'Class constructor',array(),"","       ");
		fputs($ficheroclass, "       public function $collectionname(".'$db = false'."){ \n");
		fputs($ficheroclass, '              if ($db !=false) $this->data = new '.$classname."Model(".'$db'."); \n");
		fputs($ficheroclass, "       } \n");

		fputComentario($ficheroclass,$collectionname,'Repair item',array(),"","       ");
		fputs($ficheroclass, "       public function RepairItem(".'&$item'."){ \n");

		foreach ($tabla->fields as $campo)
		fputs($ficheroclass, '            if (!isset($item->'.$campo->name.')) $item->'.$campo->name.' = null; '."\n");

		fputs($ficheroclass, "       } \n");

		fputComentario($ficheroclass,"Load",'Load somes objects to memory collection',array(),"","       ");
		fputs($ficheroclass, '       public function Load($offset = null, $limit = null, $fields = "*", $where = "" ){ '."\n");
		fputs($ficheroclass, '             if ($offset==null or $limit=null) {'."\n");
		fputs($ficheroclass, '                 if ($where == "")'."\n");
		fputs($ficheroclass, '                     $r = $this->data->getAll("*",true);'. "\n");
		fputs($ficheroclass, '                 else '."\n");
		fputs($ficheroclass, '                     $r = $this->data->getAllBy($where, "*",true);'. "\n");
		fputs($ficheroclass, '             }'. "\n");
		fputs($ficheroclass, '             else {'."\n");
		fputs($ficheroclass, '                 if ($where == "")'."\n");
		fputs($ficheroclass, '                  $r = $this->data->getLimit($limit,$offset,$fields,true);'."\n");
		fputs($ficheroclass, '                else '."\n");
		fputs($ficheroclass, '                  $r = $this->data->getLimitBy($limit,$offset,$where,$fields,true);'."\n");
		fputs($ficheroclass, '             }'."\n");
		fputs($ficheroclass, '             // $r is a collection of value objects (VO)'."\n"."\n");
		fputs($ficheroclass, '             foreach ($r as $item){'. "\n");
		fputs($ficheroclass, '                 $this->RepairItem($item);'. "\n");

		$pllaves = ""; $i = 0; foreach ($tabla->primary_key as $llave){$i++; if ($i>1) $pllaves = $pllaves.","; $pllaves = $pllaves.'$item->'.$llave;}
		fputs($ficheroclass, '                 $obj = new '.$classname.'(');

		$campsarr = explode (",",$camposphp);

		$ya = false;
		$replicas = "";
		$i = 0;
		foreach ($tabla->fields as $campo){
			if ($campo->type == 'replica'){
				$i++;
				if ($ya == true) $replicas.=',';
				$replicas.='$item->'.$campo->name;
				$ya = true;
			}
		}

		if ($i==1) $replicas.=",";
		if ($i>=1) $replicas = ",".$replicas;


		$p = true;
		foreach ($campsarr as $item){
			if ($p==false) fputs($ficheroclass, ',');
			$p=false;
			fputs($ficheroclass, '$item->'.substr($item,1));
		}
		fputs($ficheroclass, $replicas.($replicas==""?",":"").'false, $this->data, false);'."\n");

		//fputs($ficheroclass, '                 $obj = $this->getItem('.$pllaves.');'."\n");
			
		fputs($ficheroclass, '                 $this->collection[] = $obj;'."\n");
		fputs($ficheroclass, "             } \n");
		fputs($ficheroclass, "       } \n");

		fputComentario($ficheroclass,"Reload",'Reload objects to memory collection with last offset and limit',array(),"","       ");
		fputs($ficheroclass, '       public function Reload(){ '."\n");
		fputs($ficheroclass, '            $this->Load($last_offset, $last_limit); '."\n");
		fputs($ficheroclass, '       } '."\n");



		if (count($tabla->primary_key)>0) {
			fputComentario($ficheroclass,"getItem",'Load a specific object by primary keys',array(),"","       ");
			fputs($ficheroclass, '       public function getItem('.$llaves.'){ '."\n");
			fputs($ficheroclass, '             $r = $this->data->get('.$llaves.');'. "\n");
			fputs($ficheroclass, '             // $r is a value object (VO)'. "\n");
			fputs($ficheroclass, '             $obj = false;'. "\n");
			fputs($ficheroclass, '             if ($r) $obj = new '.$classname.'(');

			$campsarr = explode (",",$camposphp);

			$ya = false;
			$replicas = "";
			$i = 0;
			foreach ($tabla->fields as $campo){
				if ($campo->type == 'replica'){
					$i++;
					if ($ya == true) $replicas.=',';
					$replicas.='$r->'.$campo->name;
					$ya = true;
				}
			}

			if ($i==1) $replicas.=",";
			if ($i>=1) $replicas = ",".$replicas;


			$p = true;
			foreach ($campsarr as $item){
				if ($p==false) fputs($ficheroclass, ',');
				$p=false;
				fputs($ficheroclass, '$r->'.substr($item,1));
			}
			fputs($ficheroclass, $replicas.(trim($replicas)==""?",":"").'false, $this->data);'."\n");
			fputs($ficheroclass, '              return $obj;'."\n");
			fputs($ficheroclass, "       } \n");
		}

		fputComentario($ficheroclass,"get",'Load a specific object in collection by index',array(),"","       ");
		fputs($ficheroclass, '       public function get($idx){ '."\n");
		fputs($ficheroclass, '             if (isset($this->collection[$idx]))  '."\n");
		fputs($ficheroclass, '                  return $this->collection[$idx];'. "\n");
		fputs($ficheroclass, "             return null; \n");
		fputs($ficheroclass, "       } \n");

		if (count($tabla->primary_key)>0) {
			fputComentario($ficheroclass,"removeItem",'Remove a specific object by primary key',array(),"","       ");
			fputs($ficheroclass, '       public function removeItem('.$llaves.'){ '."\n");
			fputs($ficheroclass, '             $this->data->remove('.$llaves.');'. "\n");
			fputs($ficheroclass, "       } \n");
		}

        if (count($tabla->primary_key)>0) {
            fputComentario($ficheroclass,"remove",'Remove a specific object in collection by index',array(),"","       ");
            fputs($ficheroclass, '       public function remove($idx){ '."\n");
            fputs($ficheroclass, '             if (isset($this->collection[$idx])){  '."\n");
            fputs($ficheroclass, '                  $this->removeItem(');

		
			$ya = false;
			foreach($tabla->primary_key as $llave){
				if ($ya == true) fputs($ficheroclass, ',');
				fputs($ficheroclass, '$this->collection[$idx]->get'.capitalLetter($llave).'()');
				$ya = true;
			}
			fputs($ficheroclass, ");\n");
			fputs($ficheroclass, '                  unset($this->collection[$idx]);'. "\n");
			fputs($ficheroclass, '                  return true;'."\n");
			fputs($ficheroclass, "              } \n");
			fputs($ficheroclass, "              return false; \n");
			fputs($ficheroclass, "        } \n");
		}

		fputs($ficheroclass, "  } \n");
		fputs($ficheroclass, "?>");

		fclose ($ficheroclass);

		//ITEM

		$ficheroclass = fopen($relative_path.$carpeta."/".$classname.".php","w");
		fputs($ficheroclass, "<?php \n");
		fputs($ficheroclass, "   class $classname{ \n");
		foreach ($tabla->fields as $campo){
			fputs($ficheroclass, "       var ".'$'.$campo->name."; \n");

		}
		fputs($ficheroclass, '       var $data; '." \n");

		fputComentario($ficheroclass,$classname,'Class constructor',array());

		$ya = false;
		$replicas = "";
		$i = 0;
		foreach ($tabla->fields as $campo){
			if ($campo->type == 'replica'){
				$i++;
				if ($ya == true) $replicas.=',';
				$replicas.='$'.$campo->name." = ''";
				$ya = true;
			}
		}

		if ($i==1) $replicas.=",";

		fputs($ficheroclass, "       public function $classname($camposphp,$replicas".'$construct = true, $data = false, $load = true'."){ \n");
		fputs($ficheroclass, '              global $dbconnection;'."\n");
		fputs($ficheroclass, '              if ($data == false) $this->data = new '.$classname.'Model($dbconnection); else $this->data = $data;'."\n");
		fputs($ficheroclass, "\n");
		fputs($ficheroclass, "              //construir el objeto e insertarlo en la base de datos \n");
		fputs($ficheroclass, '              if ($construct == true) { '."\n");
		fputs($ficheroclass, '                  $replicas = $this->data'."->add($camposphp); \n");
		foreach ($tabla->fields as $campo){
			if ($campo->type=='replica')
			fputs($ficheroclass, "                    ".'$this->'.$campo->name.' = $replicas["'.$campo->name.'"]; '."\n");
			else
			fputs($ficheroclass, "                    ".'$this->'.$campo->name." = ".'$'.$campo->name."; \n");
		}
		fputs($ficheroclass, '              } '."\n");
		fputs($ficheroclass, '              else if ($load == true) { // cargar el objeto de la base de datos'."\n");
		fputs($ficheroclass, '                  $r = $this->data->get('.$llaves.'); '."\n");

		foreach ($tabla->fields as $campo){
			fputs($ficheroclass, '                  $this->'.$campo->name.' = $r->'.$campo->name.';'."\n");
		}

		fputs($ficheroclass, '              } '."\n");
		fputs($ficheroclass, '              else { // construir el objeto a partir de los parametros'."\n");

		foreach ($tabla->fields as $campo){
			fputs($ficheroclass, '                  $this->'.$campo->name.' = $'.$campo->name.';'."\n");
		}

		fputs($ficheroclass, '              }'."\n");
		fputs($ficheroclass, "       } \n");


		foreach ($tabla->fields as $campo){
			if ($campo->type!='replica'){

				fputComentario($ficheroclass,"set".capitalLetter($campo->name),'Set value of property "'.capitalLetter($campo->name).'"',array());

				fputs($ficheroclass, "       public function set".capitalLetter($campo->name).'($value){'." \n");
				fputs($ficheroclass, '              $this->'.$campo->name.' = $value; '."\n");

				if ($campo->type=='bool' || $campo->type=='boolean') {
					fputs($ficheroclass, '               if ($value == true || $value == 1 || $value == "t") $value = "true"; '."\n");
					fputs($ficheroclass, '               else if ($value == true || $value == 0 || $value == "f") $value = "false"; '."\n");
				}

				fputs($ficheroclass, '              $this->data'."->setBy(\"");

				$primero=true;
				foreach ($tabla->primary_key as $pk){
					$com ='';
					if ($entrecom[$pk]==true) $com="'";
					if ($primero==false) fputs($ficheroclass, " AND ");
					$primero=false;
					fputs($ficheroclass, "$pk = $com\"".'.$this->'.$pk.".\"$com");
				}

				$com ='';
				if (isEntreComillas($campo->type)==true) $com="'";
				fputs($ficheroclass, "\", \"".$campo->name.' = '.$com.'$value'.$com.'"); '."\n");
				fputs($ficheroclass, '       }'." \n");

			}
			fputComentario($ficheroclass,"get".capitalLetter($campo->name),'Get value of property "'.capitalLetter($campo->name).'"',array());

			fputs($ficheroclass, "       public function get".capitalLetter($campo->name).'(){'." \n");
			fputs($ficheroclass, '              $r = $this->data'."->get(");

			$primero=true;

			foreach ($tabla->primary_key as $pk){
				if ($primero==false) fputs($ficheroclass, ",");
				$primero=false;
				fputs($ficheroclass, '$this->'.$pk);
			}

			fputs($ficheroclass, "); \n");
			fputs($ficheroclass, '              $this->'.$campo->name.' = $r->'.$campo->name.'; '."\n");
			fputs($ficheroclass, '              return $this->'.$campo->name.';'."\n");
			fputs($ficheroclass, '       }'." \n");

		}

		// Analizando llaves foraneas que apuntan a la tabla actual
		foreach ($modelo->data->tables as $table){
			// todas las tablas excepto la actual
			if ($table->name != $tabla->name || $table->schema != $tabla->schema){

				//echo "      analizando tabla foranea ".$table->name."\n";
				$fks = array();
				$yes = false;

				// buscando de todas las tablas las que tienen llaves foraneas apuntando a la tabla actual
				foreach ($table->foreign_keys as $fk){
					//  echo $fk->foreign_schema.' == '.$tabla->schema."\n";
					// echo $fk->foreign_table.' == '.$tabla->name."\n";
					if (SchemasIguales($fk->foreign_schema,$tabla->schema) && $fk->foreign_table == $tabla->name){
						$fks[] = $fk;
						$yes = true;
					}
				}
				if ($yes == true){

					//    echo "                  -- apunta a la tabla ".$table->name."\n";
					//Construyendo los parametros necesarios para la funcion de agregar
					//donde los campos locales no hacen falta ni las replicas tampoco

					$params = "";
					$realparams = "";

					//recorriendo los campos de la tabla que apunta a la actual

					foreach ($table->fields as $fd){

						$sigue = false;

						foreach ($fks as $fk){
							foreach($fk->fields as $fkd){
								if ($fd->name == $fkd->localfield) {
									$sigue = true;
									break;
								}
							}
						}


						if ($sigue == false)
						if ($fd->type != 'replica'){
							if ($params != "") $params.=",";
							$params .= '$'.$fd->name;
						}
						if ($fd->type != 'replica'){
							if ($realparams != "") $realparams.=",";
							$realparams .=  '$'.$fd->name;
						}
					}

					fputs($ficheroclass, "       public function add".capitalLetter($table->schema).capitalLetter($table->name)."($params){ \n");

					foreach ($fks as $fk)
					foreach($fk->fields as $fkd)
					fputs($ficheroclass, '              $'.$fkd->localfield.' = $this->'.$fkd->foreign_key."; \n");

					$ya = false;
					$replicas = "";
					$i = 0;
					foreach ($tabla->fields as $campo){
						if ($campo->type == 'replica'){
							$i++;
							if ($ya == true) $replicas.=',';
							$replicas.='""';
							$ya = true;
						}
					}

					if ($i>0) $replicas.=',';

					fputs($ficheroclass, '              $obj = new '.capitalLetter($table->schema).capitalLetter($table->name)."Class($realparams,$replicas"."true,false); \n");
					fputs($ficheroclass, '              return $obj;'."\n");
					fputs($ficheroclass, "       } \n");


					fputs($ficheroclass, "       public function get".capitalLetter($table->schema).capitalLetter($table->name)."(){ \n");
					fputs($ficheroclass, '          $collection = new '.capitalLetter($table->schema).capitalLetter($table->name).'Collection($this->data->db);'."\n");
					fputs($ficheroclass, '          $collection->Load(null,null,"*","');

					$p = true;
					foreach ($fks as $fk)
					foreach($fk->fields as $fkd){
						$com="";
						if (llevaComillas($modelo,$table->schema,$table->name,$fkd->localfield)) $com="'";
						if ($p==false) fputs($ficheroclass," AND ");
						$p = false;
						fputs($ficheroclass, $fkd->localfield." = $com".'".$this->'.$fkd->foreign_key.'."'.$com);
					}

					fputs($ficheroclass, '");'."\n");
					fputs($ficheroclass, '           return $collection;'."\n");
					fputs($ficheroclass, "       } \n");
				}
			}
		}

		fputs($ficheroclass, "} \n");
		fputs($ficheroclass, "?>");
		fclose ($ficheroclass);
			

	}

	/**
		Encabezado fichero modelo
		*/
        /*
	$file_model = fopen($classpath."/".$fn_model,"w");

	fputs($file_model,"<?php \n");

	fputs($file_model,"\n                //Tables \n");
	foreach ($modelo->data->tables as $tabla)
	fputs($file_model,"   include_once '$carpeta/{$classname}Model.php';\n");

	fputs($file_model,"\n                //Views \n");
	foreach ($modelo->data->views as $key => $vista)
	fputs($file_model,"   include_once '$classpath/".capitalLetter($key)."DatabaseViews.php';\n");


	fputs($file_model,"\n                //Functions \n");
	foreach ($modelo->data->functions as $key => $funcion)
	fputs($file_model,"   include_once '$classpath/".capitalLetter($key)."Functions.php';\n");

	fputs($file_model,"\n");

	fputComentario($file_model,$classmodelname,'Part Model of MVC Pattern',array(),'','   ');

	fputs($file_model,"    class ".$classmodelname."{ \n \n");
	fputs($file_model,'          // Main attributes '." \n \n");
	fputs($file_model,'          var $metadata;'." \n");
	fputs($file_model,'          var $fieldnames;'." \n");
	fputs($file_model,'          var $db;'." \n");
	fputs($file_model,'          var $host;'." \n");
	fputs($file_model,'          var $port;'." \n");
	fputs($file_model,'          var $dbname;'." \n");
	fputs($file_model,'          var $user;'." \n");
	fputs($file_model,'          var $password;'." \n \n");

	fputs($file_model,'          // Parts of Model '." \n \n");

	foreach ($modelo->data->tables as $tabla)
	fputs($file_model,'          var $'.$tabla->schema.$tabla->name.";\n");

	foreach ($modelo->data->functions as $key => $funcion)
	fputs($file_model,'          var $'.$key."Functions;\n");


	foreach ($modelo->data->views as $key => $vista)
	fputs($file_model,'          var $'.$key."DatabaseViews;\n");

	fputs($file_model,"\n");
	fputComentario($file_model,"ConnectToDatabase",'Connect to database',array());

	fputs($file_model,'         public function ConnectToDatabase(){'." \n");
	fputs($file_model,'                $this->db->Connect($this->dbname,$this->user,$this->password,$this->host,$this->port); '." \n");


	fputs($file_model,"\n                //Tables \n");
	foreach ($modelo->data->tables as $tabla){
		fputs($file_model,'                 $this->'.$tabla->schema.$tabla->name." = new ".capitalLetter($tabla->schema).capitalLetter($tabla->name).'($this->db)'.";\n");
	}

	fputs($file_model,"\n                //Functions \n");
	foreach ($modelo->data->functions as $key => $funcion){
		fputs($file_model,'                 $this->'.$key."Functions = new ".capitalLetter($key).'Functions($this->db)'.";\n");
	}

	fputs($file_model,"\n                //Database Views \n");
	foreach ($modelo->data->views as $key => $vista){
		fputs($file_model,'                 $this->'.$key."DatabaseViews = new ".capitalLetter($key).'DatabaseViews($this->db)'.";\n");
	}

	fputs($file_model,'         }'." \n");
	fputs($file_model,"\n");
	fputComentario($file_model,$classmodelname,'Constructor',array());

	fputs($file_model,'          public function '.$classmodelname.'($host="'.$dbhost.'", $port='.$dbport.', $dbname="'.$dbname.'", $user="'.$dbuser.'", $password="'.$dbpass.'",$connectnow=true){'." \n");
	fputs($file_model,"\n              //Database connection \n");
	fputs($file_model,'                $this->db = new '.$clase_IDatabase.'(); '." \n");
	fputs($file_model,'                $this->host = $host;'." \n");
	fputs($file_model,'                $this->port = $port;'." \n");
	fputs($file_model,'                $this->dbname = $dbname;'." \n");
	fputs($file_model,'                $this->user = $user;'." \n");
	fputs($file_model,'                $this->password = $password;'." \n");
	fputs($file_model,'                if ($connectnow==true) $this->ConnectToDatabase(); '." \n");

	fputs($file_model,"\n                //Metadata \n");
	foreach($modelo->data->tables as $tabla){
		fputs($file_model,'                $this->fieldnames["'.depuraEsquema($tabla->schema).$modelo->data->prefix.$tabla->name.'"] = "');
		$i=0;
		foreach ($tabla->fields as $campo) {
			$i++;
			if ($i>1) fputs($file_model,',');
			fputs($file_model,$campo->name);
		}
		fputs($file_model,'";'."\n");
	}
	fputs($file_model,"              }\n");
	fputs($file_model,"    } \n");
	fputs($file_model,"?>");

	fputComentario($file_control,"Customized Methods","",array(),'     ');

	$fn_custom_methods = strtolower($fn_custom_methods);
	$file_custom_methods = fopen("src/".$fn_custom_methods,"r");
	
	if ($file_custom_methods)
	while (!feof($file_custom_methods)){
		if (($linea = fgets($file_custom_methods)))
			fputs($file_control,'       '.$linea."\n");
		else
			break;
	}

	fputs($file_control,"    } \n");
	fputs($file_control,"?>");
*/

	//funciones
	
	
	foreach ($modelo->data->functions as $key=>$funciones){
        
		$fsf = fopen($relative_path."/".dame_carpeta_de(capitalLetter($key)."Functions",$key)."/".capitalLetter($key)."Functions.php","w");
			
		fputs($fsf,"<?php \n");
			
		fputComentario($fsf,$key."Functions","Functions of schema $key",array(),"","    ");
			
		fputs($fsf, "     class ".capitalLetter($key)."Functions extends PartModel{ \n");
			
		fputs($fsf, '        var $db;'." \n");

		fputComentario($fsf,$key."Functions",'Constructor',array());
			
		fputs($fsf,'          public function '.capitalLetter($key).'Functions($db){'." \n");
		fputs($fsf,'               $this->db = $db; '." \n");
		fputs($fsf,'          }'."\n");

		$funcsnames = array();

		foreach ($funciones as $funcion){
			
			$funcname = fixFunctionName($funcion->name);
			if (isset($funcsnames[$funcname])) $funcsnames[$funcname]++; else $funcsnames[$funcname] = 1; 
			
			fputComentario($fsf, $funcion->name, "Function that ...",array(),'');
			fputs($fsf,"          public function ".$funcname.($funcsnames[$funcname]==1?'':'_v'.$funcsnames[$funcname])."(");
			$i = 0;
			if (is_array($funcion->params))
			foreach ($funcion->params as $param){
				$i++;
				if ($i>1) fputs($fsf,",");
				if ($param->name=='') $param->name = "param$i";
				fputs($fsf,'$'.$param->name);
			}
			if ($i>0) fputs($fsf, ',');

			fputs($fsf, '$asobjects = false'."){ \n");
            $msql = ' ';
            if ($funcion->return == 'USER-DEFINED') $msql = "* from ";
			fputs($fsf,'               $sql = "select '.$msql.depuraEsquema($key).$funcion->name.'(');

			$i = 0;
			if (is_array($funcion->params))
			foreach ($funcion->params as $param){
				$i++;
				if ($i>1)fputs($fsf,",");
				if (isEntreComillas($param->data_type))
				fputs($fsf,"'$".$param->name."'");
				else
				fputs($fsf,"$".$param->name);
			}
			fputs($fsf,')"; '."\n");
			fputs($fsf,'               if ($asobjects == true) return $this->db->getAsObjects($sql);'."\n");
			fputs($fsf,'               return $this->db->getAsArray($sql);'."\n");
			fputs($fsf,"           } \n");
		}

		fputs($fsf, "     } \n");
			
		fclose($fsf);

	}

if(!isset($_SERVER['argv']) || php_sapi_name()!=='cli')
	echo "</body></html>";
?>
