<?php 
/** 
  * @name: PartModel 
  * @desc: Father of other class model 
  * @updated: 20/06/10 10:40:54 
  * @version: 1.0 
  */ 
   class PartModel { 
 
          var $db; 
 
          public function __construct($db){ 
                $this->db = $db;  
          } 
          /**
            * @name getAnything
            * @descrip Get anything SQL query
            */

             public function getAnything($sql,$asobjects=false){
               if ($asobjects==true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
          } 

          /**
            * @name getWHEREPartOfSearchFrase
            * @descrip Construct WHERE part of SQL query from phrase ...
            */

        function getWHEREPartOfSearchFrase($frase, $fields, array &$params){
           $arr = explode(",", $fields); $words = explode(' ', trim((string)$frase));
            foreach ($arr as $k => $field) $arr[$k] = PartModel::sqlIdentifier(trim($field), true);
            $parteand = ' TRUE ';
            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)=='"') {
                     $params[] = "%".str_replace('"','',$word)."%";
                     $parteand = $parteand.' AND CAST('.$field.' AS text) LIKE $'.count($params).' ';
                }
             }
             $parteor = ' AND (TRUE';

            foreach($arr as $field) foreach($words as $word){
                if (substr($word,0,1)!='"') {
                     $params[] = "%".$word."%";
                     $parteor = $parteor.' OR CAST('.$field.' AS text) LIKE $'.count($params).' ';
                }
            }

            $parteor = $parteor.')';
            return $parteand.$parteor;
        }

          /**
            * @name getAllWithPhraseIn
            * @descrip Get all records from table that cotains this phrase...
            * $extrawhere is a trusted SQL fragment; its values go in $extraparams ($1..$n).
            */

          public function getAllWithPhraseIn($table,$fields,$phrase,$extrawhere="true", $showfields="*",$asobjects=false, array $extraparams = array()){
              $params = array_values($extraparams);
              $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
              return $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($showfields)." FROM ".PartModel::sqlIdentifier($table, true)." WHERE ($extrawhere) AND ($phrasepart);", $params, $asobjects);
          }

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
}
 ?> 
