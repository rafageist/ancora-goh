<?php 
   class PlantillaDeActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: plantilla_de_actividad 
           * @desc: Work with table "plantilla_de_actividad" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM plantilla_de_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->exige_profesor)) {if ($obj->exige_profesor == "f") $obj->exige_profesor = false; else $obj->exige_profesor = true; }
                  if (isset($obj->exige_lugar)) {if ($obj->exige_lugar == "f") $obj->exige_lugar = false; else $obj->exige_lugar = true; }
             } else {
                  if (isset($obj["exige_profesor"])) {if ($obj["exige_profesor"] == "f") $obj["exige_profesor"] = false; else $obj["exige_profesor"] = true; }
                  if (isset($obj["exige_lugar"])) {if ($obj["exige_lugar"] == "f") $obj["exige_lugar"] = false; else $obj["exige_lugar"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar){ 
             $replicas = array();
             $id = getReplica("plantilla_de_actividad");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO plantilla_de_actividad (id,desglose,orden,clasificacion,exige_profesor,exige_lugar) VALUES ($1,$2,$3,$4,$5,$6);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($desglose, "text"), PartModel::sqlValue($orden, "number"), PartModel::sqlValue($clasificacion, "text"), PartModel::sqlValue($exige_profesor, "bool"), PartModel::sqlValue($exige_lugar, "bool")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM plantilla_de_actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM plantilla_de_actividad WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM plantilla_de_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM plantilla_de_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE plantilla_de_actividad SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE plantilla_de_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE plantilla_de_actividad SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE plantilla_de_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM plantilla_de_actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = public.clasificacion_de_actividad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesglose($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = public.desglose.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = public.clasificacion_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesgloseBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = public.desglose.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "plantilla_de_actividad.id,plantilla_de_actividad.desglose,plantilla_de_actividad.orden,plantilla_de_actividad.clasificacion,plantilla_de_actividad.exige_profesor,plantilla_de_actividad.exige_lugar,public.clasificacion_de_actividad.id,public.clasificacion_de_actividad.descripcion,public.clasificacion_de_actividad.duracion,public.clasificacion_de_actividad.creador,public.clasificacion_de_actividad.prioridad,public.clasificacion_de_actividad.gasto";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = public.clasificacion_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesgloseByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "plantilla_de_actividad.id,plantilla_de_actividad.desglose,plantilla_de_actividad.orden,plantilla_de_actividad.clasificacion,plantilla_de_actividad.exige_profesor,plantilla_de_actividad.exige_lugar,public.desglose.id,public.desglose.asignatura,public.desglose.periodo,public.desglose.min_dias,public.desglose.max_dias,public.desglose.respetar_orden,public.desglose.mismo_dia";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = public.desglose.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>