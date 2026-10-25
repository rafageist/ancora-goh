<?php 
   class DesgloseModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: desglose 
           * @desc: Work with table "desglose" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM desglose; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->respetar_orden)) {if ($obj->respetar_orden == "f") $obj->respetar_orden = false; else $obj->respetar_orden = true; }
                  if (isset($obj->mismo_dia)) {if ($obj->mismo_dia == "f") $obj->mismo_dia = false; else $obj->mismo_dia = true; }
             } else {
                  if (isset($obj["respetar_orden"])) {if ($obj["respetar_orden"] == "f") $obj["respetar_orden"] = false; else $obj["respetar_orden"] = true; }
                  if (isset($obj["mismo_dia"])) {if ($obj["mismo_dia"] == "f") $obj["mismo_dia"] = false; else $obj["mismo_dia"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia){ 
             $replicas = array();
             $id = getReplica("desglose");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO desglose (id,asignatura,periodo,min_dias,max_dias,respetar_orden,mismo_dia) VALUES ($1,$2,$3,$4,$5,$6,$7);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($asignatura, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($min_dias, "number"), PartModel::sqlValue($max_dias, "number"), PartModel::sqlValue($respetar_orden, "bool"), PartModel::sqlValue($mismo_dia, "bool")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM desglose WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM desglose WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM desglose WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM desglose", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE desglose SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE desglose SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE desglose SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE desglose SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM desglose WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM desglose WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignatura($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = public.asignatura.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.periodo ON desglose.periodo = public.periodo.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = public.asignatura.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.periodo ON desglose.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "desglose.id,desglose.asignatura,desglose.periodo,desglose.min_dias,desglose.max_dias,desglose.respetar_orden,desglose.mismo_dia,public.asignatura.id,public.asignatura.descripcion,public.asignatura.especialidad,public.asignatura.nivel,public.asignatura.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = public.asignatura.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "desglose.id,desglose.asignatura,desglose.periodo,desglose.min_dias,desglose.max_dias,desglose.respetar_orden,desglose.mismo_dia,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM desglose INNER JOIN public.periodo ON desglose.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>