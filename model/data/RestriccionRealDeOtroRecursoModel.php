<?php 
   class RestriccionRealDeOtroRecursoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_real_de_otro_recurso 
           * @desc: Work with table "restriccion_real_de_otro_recurso" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_real_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_real_de_otro_recurso; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->por_actividad)) {if ($obj->por_actividad == "f") $obj->por_actividad = false; else $obj->por_actividad = true; }
             } else {
                  if (isset($obj["por_actividad"])) {if ($obj["por_actividad"] == "f") $obj["por_actividad"] = false; else $obj["por_actividad"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "restriccion_real_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO restriccion_real_de_otro_recurso (restriccion_nominal,periodicidad,fecha,hora_inicial,hora_final,por_actividad,actividad,objeto) VALUES ($1,$2,$3,$4,$5,$6,$7,$8);", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text"), PartModel::sqlValue($fecha, "number"), PartModel::sqlValue($hora_inicial, "number"), PartModel::sqlValue($hora_final, "number"), PartModel::sqlValue($por_actividad, "bool"), PartModel::sqlValue($actividad, "text"), PartModel::sqlValue($objeto, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_real_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal,$periodicidad){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_real_de_otro_recurso WHERE restriccion_nominal = $1 AND periodicidad = $2;", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_real_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_real_de_otro_recurso WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_real_de_otro_recurso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_real_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_real_de_otro_recurso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_real_de_otro_recurso SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_real_de_otro_recurso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_real_de_otro_recurso SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_real_de_otro_recurso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_real_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal,$periodicidad){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_real_de_otro_recurso WHERE restriccion_nominal = $1 AND periodicidad = $2;", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_real_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_real_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_real_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_real_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_real_de_otro_recurso.objeto = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.periodicidad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.periodicidad ON restriccion_real_de_otro_recurso.periodicidad = public.periodicidad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_real_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_real_de_otro_recurso.objeto = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.periodicidad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.periodicidad ON restriccion_real_de_otro_recurso.periodicidad = public.periodicidad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.restriccion_de_otro_recurso by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_real_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_otro_recurso.restriccion_nominal,restriccion_real_de_otro_recurso.periodicidad,restriccion_real_de_otro_recurso.fecha,restriccion_real_de_otro_recurso.hora_inicial,restriccion_real_de_otro_recurso.hora_final,restriccion_real_de_otro_recurso.por_actividad,restriccion_real_de_otro_recurso.actividad,restriccion_real_de_otro_recurso.objeto,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_real_de_otro_recurso.objeto = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.periodicidad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_otro_recurso.restriccion_nominal,restriccion_real_de_otro_recurso.periodicidad,restriccion_real_de_otro_recurso.fecha,restriccion_real_de_otro_recurso.hora_inicial,restriccion_real_de_otro_recurso.hora_final,restriccion_real_de_otro_recurso.por_actividad,restriccion_real_de_otro_recurso.actividad,restriccion_real_de_otro_recurso.objeto,public.periodicidad.id,public.periodicidad.periodo,public.periodicidad.dia";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.periodicidad ON restriccion_real_de_otro_recurso.periodicidad = public.periodicidad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_otro_recurso" and "public.restriccion_de_otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_otro_recurso.restriccion_nominal,restriccion_real_de_otro_recurso.periodicidad,restriccion_real_de_otro_recurso.fecha,restriccion_real_de_otro_recurso.hora_inicial,restriccion_real_de_otro_recurso.hora_final,restriccion_real_de_otro_recurso.por_actividad,restriccion_real_de_otro_recurso.actividad,restriccion_real_de_otro_recurso.objeto,public.restriccion_de_otro_recurso.id,public.restriccion_de_otro_recurso.otro_recurso,public.restriccion_de_otro_recurso.periodo,public.restriccion_de_otro_recurso.dia,public.restriccion_de_otro_recurso.turno,public.restriccion_de_otro_recurso.valor,public.restriccion_de_otro_recurso.por_actividad,public.restriccion_de_otro_recurso.actividad";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_real_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>