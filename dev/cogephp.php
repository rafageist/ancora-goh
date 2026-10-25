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

	fputs($file_control,'          public function __construct($host="'.$dbhost.'", $port='.$dbport.', $dbname="'.$dbname.'", $user="'.$dbuser.'", $password="'.$dbpass.'",$connectnow=true){'." \n");
	fputs($file_control,'                $this->model = new '.$classmodelname.'($host,$port,$dbname,$user,$password,$connectnow);'." \n");
	fputs($file_control,'          }'." \n");
*/
	$file_model = fopen($relative_path.str_replace("..",".",dame_carpeta_de("PartModel", "")."/PartModel.php"),"w");
	

	fputs($file_model,"<?php \n");
	fputComentario($file_model,'PartModel','Father of other class model',array(),"","");

	fputs($file_model,"   class PartModel { \n \n");
	fputs($file_model,'          var $db;'." \n \n");
	fputs($file_model,'          public function __construct($db){'." \n");
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

	// #35: the search phrase is bound ($n placeholders appended to $params);
	// the field list and table name are checked identifiers. The historical
	// semantics are kept: words starting with a double quote must all match
	// (AND); the other words are OR-ed into "(TRUE OR ...)", which never
	// filtered anything.
	fputs($file_model,
'        function getWHEREPartOfSearchFrase($frase, $fields, array &$params){
           $arr = explode(",", $fields); $words = explode(\' \', trim((string)$frase));
            foreach ($arr as $k => $field) $arr[$k] = PartModel::sqlIdentifier(trim($field), true);
            $parteand = \' TRUE \';
            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)==\'"\') {
                     $params[] = "%".str_replace(\'"\',\'\',$word)."%";
                     $parteand = $parteand.\' AND CAST(\'.$field.\' AS text) LIKE $\'.count($params).\' \';
                }
             }
             $parteor = \' AND (TRUE\';

            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)!=\'"\') {
                     $params[] = "%".$word."%";
                     $parteor = $parteor.\' OR CAST(\'.$field.\' AS text) LIKE $\'.count($params).\' \';
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
        '            * $extrawhere is a trusted SQL fragment; its values go in $extraparams ($1..$n).'."\n".
        '            */'."\n".
        "\n".
        '          public function getAllWithPhraseIn($table,$fields,$phrase,$extrawhere="true", $showfields="*",$asobjects=false, array $extraparams = array()){'."\n".
        '              $params = array_values($extraparams);'."\n".
        '              $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);'."\n".
        '              return $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($showfields)." FROM ".PartModel::sqlIdentifier($table, true)." WHERE ($extrawhere) AND ($phrasepart);", $params, $asobjects);'."\n".
        '          }'."\n");

	// #35: parameterized execution and strict value conversion, used by
	// every generated query that carries a value.
	fputs($file_model, <<<'PHPCODE'

          /**
            * @name getAnythingParams
            * @descrip Run a parameterized query: $1, $2, ... in $sql, the values
            *          in $params. Values are sent separately from the SQL text
            *          and never interpolated. A failed query throws, so an error
            *          is not mistaken for an empty result. (#35)
            */
          public function getAnythingParams($sql, array $params, $asobjects = false){
               if ($asobjects == true) $r = $this->db->getAsObjectsParams($sql, $params);
               else $r = $this->db->getAsArrayParams($sql, $params);
               if ($r === false) throw new RuntimeException("query failed: ".$sql);
               return $r;
          }

          /**
            * @name sqlValue
            * @descrip Convert a PHP value into a bound parameter of the given
            *          kind: "text", "number" or "bool". Only null (===) is SQL
            *          NULL; 0, "0" and "" are values. Anything that is not a
            *          valid value of the kind is rejected. (#35)
            */
          public static function sqlValue($value, $kind = "text"){
               if ($value === null) return null;
               if ($kind == "bool") {
                    if ($value === true || $value === 1 || $value === "1") return "t";
                    if ($value === false || $value === 0 || $value === "0") return "f";
                    if (is_string($value)) {
                         $v = strtolower(trim($value));
                         if (in_array($v, array("t", "true", "y", "yes", "on"), true)) return "t";
                         if (in_array($v, array("f", "false", "n", "no", "off"), true)) return "f";
                    }
                    throw new InvalidArgumentException("not a boolean value");
               }
               if ($kind == "number") {
                    if (is_int($value)) return (string)$value;
                    if (is_float($value) && is_finite($value)) return (string)$value;
                    if (is_string($value) && is_numeric($value)) return trim($value);
                    throw new InvalidArgumentException("not a numeric value");
               }
               if (is_string($value)) return $value;
               if (is_int($value) || (is_float($value) && is_finite($value))) return (string)$value;
               throw new InvalidArgumentException("not a text value");
          }

          /**
            * @name sqlIdentifier
            * @descrip Check a column or table name: letters, digits and "_",
            *          optionally qualified (schema.table.column) when
            *          $qualified. (#35)
            */
          public static function sqlIdentifier($name, $qualified = false){
               $part = "[A-Za-z_][A-Za-z0-9_]*";
               $re = $qualified ? "/^$part(\.$part){0,2}$/" : "/^$part$/";
               if (is_string($name) && preg_match($re, $name)) return $name;
               throw new InvalidArgumentException("invalid SQL identifier");
          }

          /**
            * @name sqlSelectList
            * @descrip Check a select list ("*", columns, function calls and
            *          aliases). Quotes, literals, placeholders, semicolons and
            *          comments are rejected. It is still SQL text: never build
            *          it from request values. (#35)
            */
          public static function sqlSelectList($fields){
               if (is_string($fields) && preg_match('/^[A-Za-z0-9_.,*() ]+$/', $fields)) return $fields;
               throw new InvalidArgumentException("invalid SQL select list");
          }

PHPCODE
);


	fputs($file_model,"}\n ?> \n");
	fclose($file_model);

	foreach ($modelo->data->views as $key => $tablas){

		$file_model = fopen($relative_path.dame_carpeta_de(capitalLetter($key).'DatabaseViews',"vistas")."/".capitalLetter($key).'DatabaseViews.php',"w");

		fputs($file_model,"<?php \n");

		fputs($file_model,'   class '.capitalLetter($key)."DatabaseViews extends PartModel { \n \n");

		fputComentario($file_model,capitalLetter($key)."DatabaseViews",'Constructor',array(),'','       ');

		fputs($file_model,'          public function __construct($db){'." \n");
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

	

	// #35: kind of bound parameter for a model field type (PartModel::sqlValue):
	// booleans, the types that were interpolated unquoted, and text.
	function sqlKindOf($type){
		if ($type == 'bool' || $type == 'boolean') return 'bool';
		if (!isEntreComillas($type)) return 'number';
		return 'text';
	}

	// #35: PHP expression binding $expr as a parameter of a field type
	function sqlBind($expr, $type){
		return 'PartModel::sqlValue('.$expr.', "'.sqlKindOf($type).'")';
	}

	// #35: type of a field of a table in the model ('string' if unknown)
	function fieldTypeOf($tabla, $name){
		foreach ($tabla->fields as $campo) if ($campo->name == $name) return $campo->type;
		return 'string';
	}

	// #35: "pk1 = $n AND pk2 = $n+1" for the primary key, numbered after $offset
	function pkWhere($tabla, $offset = 0){
		$parts = array();
		foreach ($tabla->primary_key as $llave) $parts[] = $llave.' = $'.($offset + count($parts) + 1);
		return implode(' AND ', $parts);
	}

	// #35: bound values of the primary key, read from $prefix.name
	// ("$" for parameters, "$this->" for properties)
	function pkBinds($tabla, $prefix){
		$binds = array();
		foreach ($tabla->primary_key as $llave) $binds[] = sqlBind($prefix.$llave, fieldTypeOf($tabla, $llave));
		return implode(', ', $binds);
	}

	foreach ($modelo->data->tables as $tabla){
		$classname = getClassNameOf($tabla->schema, $tabla->name);
		$carpeta = dame_carpeta_de($classname, $tabla->schema);
	     
		$file_model = fopen($relative_path."$carpeta/{$classname}Model.php","w");

		fputs($file_model,"<?php \n");

		fputs($file_model,'   class '.$classname."Model extends PartModel { \n \n");

		fputs($file_model,'          public function __construct($db){'." \n");
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
		fputs($file_model,'              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);'."\n");
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

		// #35: every value is a bound parameter converted by PartModel::sqlValue
		// (booleans included: "t"/"f"), instead of being interpolated into the
		// SQL text. md5-typed fields are stored with password_hash() (#14).
		$i=0;
		$entrecom = array();
		$placeholders = array();
		$binds = array();
		foreach ($tabla->fields as $campo){
			$i++;
			$entrecom[$campo->name] = isEntreComillas($campo->type);
			$placeholders[] = '$'.$i;
			if ($campo->type=='md5')
				$binds[] = 'PasswordUtil::hash($'.$campo->name.')';
			else
				$binds[] = sqlBind('$'.$campo->name, $campo->type);
		}

		fputs($file_model,'             $r = $this->getAnythingParams("'."INSERT INTO $tnombre ($campossql) VALUES (".implode(",",$placeholders).');", array('.implode(", ",$binds).'));'."\n");
		fputs($file_model,'             return $replicas; '."\n".'            }'."\n");

		//Eliminar uno

		if (count($tabla->primary_key)>0) {
			fputComentario($file_model,"remove",'Remove "'.$tnombre.'"',array());
			fputs($file_model,"          public function remove($llaves){ \n");
			fputs($file_model,'                  return $this->getAnythingParams("'."DELETE FROM $tnombre WHERE ".pkWhere($tabla).';", array('.pkBinds($tabla, '$').'));'."\n".'            }'."\n");
		}
			
		//Eliminar varios

		fputComentario($file_model,"remove","Remove '$tnombre' by ...",array());
		fputs($file_model,
         "          public function removeBy".'($where, $asobjects = false, $params = null){'."\n".
         '                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM '.$tnombre.' WHERE $where;", $params, $asobjects);'."\n".
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
         "          public function set".'($set, $asobjects = false, $params = null){'."\n".
         '               if (is_array($params)) return $this->getAnythingParams("UPDATE '.$tnombre.' SET $set;", $params, $asobjects);'."\n".
         '               return $this->getAnything("UPDATE '.$tnombre.' SET $set;",$asobjects);'."\n".
         "          } \n");

		//Actualizar segun...
		fputComentario($file_model,"set","Set '$tnombre' by ...",array());
		fputs($file_model,
         "          public function setBy".'($where, $set, $asobjects = false, $params = null){'."\n".
         '               if (is_array($params)) return $this->getAnythingParams("UPDATE '.$tnombre.' SET $set WHERE $where;", $params, $asobjects);'."\n".
         '               return $this->getAnything("UPDATE '.$tnombre.' SET $set WHERE $where;");'."\n".
         "          } \n");
			
		//Obtener entidad
		if (count($tabla->primary_key)>0) {
			fputComentario($file_model,"get",'Get specified "'.$tnombre.'"',array());
			fputs($file_model,"          public function get($llaves){ \n");
			fputs($file_model,'                  $r = $this->getAnythingParams("'."SELECT * FROM $tnombre WHERE ".pkWhere($tabla).';", array('.pkBinds($tabla, '$').'), true);'."\n");
			if ($cantcamposbool > 0)
                fputs($file_model,'                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;'."\n".'            }'."\n");
            else
                fputs($file_model,'                   if (isset($r[0])) return $r[0]; else return false;'."\n".'            }'."\n");
		}
			
		// #35: reads. $fields is checked by PartModel::sqlSelectList; $where is a
		// trusted SQL fragment whose values are bound when $params ($1..$n) is
		// given; limit and offset are always bound.
		$code_repairBool = 'if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);';
		$repair = ($cantcamposbool > 0) ? '               '.$code_repairBool."\n" : '';

		//Obtener todos
		fputComentario($file_model,"getAll","Get all '$tnombre'",array());
		fputs($file_model,
		 "          public function getAll".'($fields = "*", $asobjects = false){'."\n".
		 '               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM '.$tnombre.';",$asobjects);'."\n".
		 $repair.
		 '               return $r;'."\n".
		 "          } \n");

		//Obtener segun...
		fputComentario($file_model,"getAllBy","Get '$tnombre' by ...",array());
		fputs($file_model,
		 '          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){'."\n".
		 '               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM '.$tnombre.' WHERE $where;";'."\n".
		 '               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);'."\n".
		 '               else $r = $this->getAnything($sql, $asobjects);'."\n".
		 $repair.
		 '               return $r;'."\n".
		 "          } \n");

		//Obtener todos limitados...
		fputComentario($file_model,"getLimit","Limited get '$tnombre'",array());
		fputs($file_model,
		 "          public function getLimit".'($limit, $offset, $fields = "*", $asobjects = false){'."\n".
		 '               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM '.$tnombre.' LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);'."\n".
		 $repair.
		 '               return $r;'."\n".
		 "          } \n");

		//Obtener todos limitados y segun...
		fputComentario($file_model,"getLimitBy","Limited get '$tnombre' by ...",array());
		fputs($file_model,
		 "          public function getLimitBy".'($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){'."\n".
		 '               $params = is_array($params) ? array_values($params) : array();'."\n".
		 '               $n = count($params);'."\n".
		 '               $params[] = PartModel::sqlValue($limit, "number");'."\n".
		 '               $params[] = PartModel::sqlValue($offset, "number");'."\n".
		 '               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM '.$tnombre.' WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);'."\n".
		 $repair.
		 '               return $r;'."\n".
		 "          } \n");

		// get joins: plain, by a WHERE fragment, and by a search phrase
		foreach (array('', 'By', 'ByPhrase') as $variante){
			$funcs = array();
			foreach($tabla->foreign_keys as $foranea){

				$tforanea = depuraEsquema($foranea->foreign_schema).$modelo->data->prefix.$foranea->foreign_table;

				$schm = capitalLetter($foranea->foreign_schema);
				if ($schm == "Public") $schm = "";

				$fsufijo = capitalLetter($schm).$modelo->data->prefix.capitalLetter($foranea->foreign_table);

				$fname = "getJoinWith$fsufijo";
				while (isset($funcs[$fname])) $fname .= "A";
				$funcs[$fname] = true;

				$on = array();
				foreach ($foranea->fields as $campo) $on[] = $tnombre.'.'.$campo->localfield.' = '.$tforanea.'.'.$campo->foreign_key;
				$from = $tnombre.' INNER JOIN '.$tforanea.' ON '.implode(' AND ', $on);

				if ($variante == '') {
					fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.'"',array());
					fputs($file_model,"          public function $fname".'($fields = "*", $asobjects = false){'."\n".
					 '               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM '.$from.';", $asobjects);'."\n");
				}
				else if ($variante == 'By') {
					fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.' by ..."',array());
					fputs($file_model,"          public function $fname".'By($where, $fields = "*", $asobjects = false, $params = null){'."\n".
					 '               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM '.$from.' WHERE $where;";'."\n".
					 '               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);'."\n".
					 '               else $r = $this->getAnything($sql, $asobjects);'."\n");
				}
				else {
					// "*" searches every column of both tables (the old code called
					// explode() with one argument on an undefined property)
					$todos = array();
					foreach ($tabla->fields as $campo) $todos[] = $tnombre.'.'.$campo->name;
					foreach ($modelo->data->tables as $tf)
						if (SchemasIguales($tf->schema, $foranea->foreign_schema) && $tf->name == $foranea->foreign_table)
							foreach ($tf->fields as $campo) $todos[] = $tforanea.'.'.$campo->name;
					fputComentario($file_model,"getJoin",'Inner join between "'.$tnombre.'" and "'.$tforanea.', by phrase ..."',array());
					fputs($file_model,"          public function $fname".'ByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){'."\n".
					 '               if ($fields == "*") $fields = "'.implode(',', $todos).'";'."\n".
					 '               $params = is_array($params) ? array_values($params) : array();'."\n".
					 '               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);'."\n".
					 '               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM '.$from.' WHERE ($where) AND ($phrasepart);", $params, $asobjects);'."\n");
				}
				fputs($file_model, $repair);
				fputs($file_model,'             return $r; '."\n");
				fputs($file_model,"          } \n");
			}
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
		fputs($ficheroclass, "       public function __construct(".'$db = false'."){ \n");
		fputs($ficheroclass, '              if ($db !=false) $this->data = new '.$classname."Model(".'$db'."); \n");
		fputs($ficheroclass, "       } \n");

		fputComentario($ficheroclass,$collectionname,'Repair item',array(),"","       ");
		fputs($ficheroclass, "       public function RepairItem(".'&$item'."){ \n");

		foreach ($tabla->fields as $campo)
		fputs($ficheroclass, '            if (!isset($item->'.$campo->name.')) $item->'.$campo->name.' = null; '."\n");

		fputs($ficheroclass, "       } \n");

		fputComentario($ficheroclass,"Load",'Load somes objects to memory collection',array(),"","       ");
		// #35: $params binds the values of $where ($1..$n)
		fputs($ficheroclass, '       public function Load($offset = null, $limit = null, $fields = "*", $where = "", $params = null ){ '."\n");
		fputs($ficheroclass, '             if ($offset==null or $limit=null) {'."\n");
		fputs($ficheroclass, '                 if ($where == "")'."\n");
		fputs($ficheroclass, '                     $r = $this->data->getAll("*",true);'. "\n");
		fputs($ficheroclass, '                 else '."\n");
		fputs($ficheroclass, '                     $r = $this->data->getAllBy($where, "*",true, $params);'. "\n");
		fputs($ficheroclass, '             }'. "\n");
		fputs($ficheroclass, '             else {'."\n");
		fputs($ficheroclass, '                 if ($where == "")'."\n");
		fputs($ficheroclass, '                  $r = $this->data->getLimit($limit,$offset,$fields,true);'."\n");
		fputs($ficheroclass, '                else '."\n");
		fputs($ficheroclass, '                  $r = $this->data->getLimitBy($limit,$offset,$where,$fields,true, $params);'."\n");
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

		fputs($ficheroclass, "       public function __construct($camposphp,$replicas".'$construct = true, $data = false, $load = true'."){ \n");
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
				if ($campo->type=='md5') // #14: store password_hash(), never the plain value
					fputs($ficheroclass, '              if (!is_string($value) || $value === \'\') return false; '."\n".'              $value = PasswordUtil::hash($value); '."\n");
				fputs($ficheroclass, '              $this->'.$campo->name.' = $value; '."\n");

				// #35: the new value and the key are bound parameters
				$valor = ($campo->type=='md5') ? '$value' : sqlBind('$value', $campo->type);
				fputs($ficheroclass, '              $this->data->getAnythingParams("UPDATE '.$tnombre.' SET '.$campo->name.' = $1 WHERE '.pkWhere($tabla, 1).';", array('.$valor.', '.pkBinds($tabla, '$this->').')); '."\n");
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
					// #35: the key values are bound parameters
					$conds = array(); $binds = array();
					foreach ($fks as $fk)
					foreach($fk->fields as $fkd){
						$conds[] = $fkd->localfield.' = $'.(count($conds) + 1);
						$binds[] = sqlBind('$this->'.$fkd->foreign_key, fieldTypeOf($table, $fkd->localfield));
					}
					fputs($ficheroclass, '          $collection->Load(null,null,"*","'.implode(' AND ', $conds).'", array('.implode(', ', $binds).'));'."\n");
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

	fputs($file_model,'          public function __construct($host="'.$dbhost.'", $port='.$dbport.', $dbname="'.$dbname.'", $user="'.$dbuser.'", $password="'.$dbpass.'",$connectnow=true){'." \n");
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
			
		fputs($fsf,'          public function __construct($db){'." \n");
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
			// #35: arguments are bound parameters. They are untyped, like the
			// quoted literals used before, so PostgreSQL resolves the function
			// from its declared parameter types.
			$placeholders = array(); $binds = array();
			if (is_array($funcion->params))
			foreach ($funcion->params as $param){
				$placeholders[] = '$'.(count($placeholders) + 1);
				$binds[] = sqlBind('$'.$param->name, $param->data_type);
			}
			fputs($fsf,'               $sql = "select '.$msql.depuraEsquema($key).$funcion->name.'('.implode(',', $placeholders).')"; '."\n");
			fputs($fsf,'               return $this->getAnythingParams($sql, array('.implode(', ', $binds).'), $asobjects);'."\n");
			fputs($fsf,"           } \n");
		}

		fputs($fsf, "     } \n");
			
		fclose($fsf);

	}

if(!isset($_SERVER['argv']) || php_sapi_name()!=='cli')
	echo "</body></html>";
?>
