<?php 
   class RestriccionRealDeClasifActModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_real_de_clasif_act 
           * @desc: Work with table "restriccion_real_de_clasif_act" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_real_de_clasif_act; "); 
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
            * @desc: Add "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO restriccion_real_de_clasif_act (restriccion_nominal,periodicidad,fecha,hora_inicial,hora_final,por_actividad,actividad,objeto) VALUES ($1,$2,$3,$4,$5,$6,$7,$8);", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text"), PartModel::sqlValue($fecha, "number"), PartModel::sqlValue($hora_inicial, "number"), PartModel::sqlValue($hora_final, "number"), PartModel::sqlValue($por_actividad, "bool"), PartModel::sqlValue($actividad, "text"), PartModel::sqlValue($objeto, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal,$periodicidad){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_real_de_clasif_act WHERE restriccion_nominal = $1 AND periodicidad = $2;", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_real_de_clasif_act WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_real_de_clasif_act WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_real_de_clasif_act", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_real_de_clasif_act SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_real_de_clasif_act SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_real_de_clasif_act SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_real_de_clasif_act SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal,$periodicidad){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_real_de_clasif_act WHERE restriccion_nominal = $1 AND periodicidad = $2;", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($periodicidad, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = public.clasificacion_de_actividad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = public.periodicidad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifAct($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = public.restriccion_de_clasif_act.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = public.clasificacion_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = public.periodicidad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifActBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = public.restriccion_de_clasif_act.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_clasif_act.restriccion_nominal,restriccion_real_de_clasif_act.periodicidad,restriccion_real_de_clasif_act.fecha,restriccion_real_de_clasif_act.hora_inicial,restriccion_real_de_clasif_act.hora_final,restriccion_real_de_clasif_act.por_actividad,restriccion_real_de_clasif_act.actividad,restriccion_real_de_clasif_act.objeto,public.clasificacion_de_actividad.id,public.clasificacion_de_actividad.descripcion,public.clasificacion_de_actividad.duracion,public.clasificacion_de_actividad.creador,public.clasificacion_de_actividad.prioridad,public.clasificacion_de_actividad.gasto";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = public.clasificacion_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_clasif_act.restriccion_nominal,restriccion_real_de_clasif_act.periodicidad,restriccion_real_de_clasif_act.fecha,restriccion_real_de_clasif_act.hora_inicial,restriccion_real_de_clasif_act.hora_final,restriccion_real_de_clasif_act.por_actividad,restriccion_real_de_clasif_act.actividad,restriccion_real_de_clasif_act.objeto,public.periodicidad.id,public.periodicidad.periodo,public.periodicidad.dia";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = public.periodicidad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifActByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_real_de_clasif_act.restriccion_nominal,restriccion_real_de_clasif_act.periodicidad,restriccion_real_de_clasif_act.fecha,restriccion_real_de_clasif_act.hora_inicial,restriccion_real_de_clasif_act.hora_final,restriccion_real_de_clasif_act.por_actividad,restriccion_real_de_clasif_act.actividad,restriccion_real_de_clasif_act.objeto,public.restriccion_de_clasif_act.id,public.restriccion_de_clasif_act.clasificacion,public.restriccion_de_clasif_act.periodo,public.restriccion_de_clasif_act.dia,public.restriccion_de_clasif_act.turno,public.restriccion_de_clasif_act.valor";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = public.restriccion_de_clasif_act.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>