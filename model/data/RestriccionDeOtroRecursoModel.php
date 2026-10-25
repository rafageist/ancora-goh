<?php 
   class RestriccionDeOtroRecursoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_de_otro_recurso 
           * @desc: Work with table "restriccion_de_otro_recurso" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_de_otro_recurso; "); 
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
            * @desc: Add "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad){ 
             $replicas = array();
             $id = getReplica("restriccion_de_otro_recurso");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO restriccion_de_otro_recurso (id,otro_recurso,periodo,dia,turno,valor,por_actividad,actividad) VALUES ($1,$2,$3,$4,$5,$6,$7,$8);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($otro_recurso, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($valor, "number"), PartModel::sqlValue($por_actividad, "bool"), PartModel::sqlValue($actividad, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_de_otro_recurso WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_de_otro_recurso WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_de_otro_recurso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_de_otro_recurso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_otro_recurso SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_otro_recurso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_otro_recurso SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_otro_recurso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_de_otro_recurso WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = public.actividad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = public.periodo.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = public.actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_otro_recurso.id,restriccion_de_otro_recurso.otro_recurso,restriccion_de_otro_recurso.periodo,restriccion_de_otro_recurso.dia,restriccion_de_otro_recurso.turno,restriccion_de_otro_recurso.valor,restriccion_de_otro_recurso.por_actividad,restriccion_de_otro_recurso.actividad,public.actividad.id,public.actividad.plantilla,public.actividad.grupo,public.actividad.profesor,public.actividad.lugar,public.actividad.dia,public.actividad.turno,public.actividad.momento_de_colocacion,public.actividad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = public.actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_otro_recurso.id,restriccion_de_otro_recurso.otro_recurso,restriccion_de_otro_recurso.periodo,restriccion_de_otro_recurso.dia,restriccion_de_otro_recurso.turno,restriccion_de_otro_recurso.valor,restriccion_de_otro_recurso.por_actividad,restriccion_de_otro_recurso.actividad,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_otro_recurso.id,restriccion_de_otro_recurso.otro_recurso,restriccion_de_otro_recurso.periodo,restriccion_de_otro_recurso.dia,restriccion_de_otro_recurso.turno,restriccion_de_otro_recurso.valor,restriccion_de_otro_recurso.por_actividad,restriccion_de_otro_recurso.actividad,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>