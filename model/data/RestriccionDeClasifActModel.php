<?php 
   class RestriccionDeClasifActModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_de_clasif_act 
           * @desc: Work with table "restriccion_de_clasif_act" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_de_clasif_act; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($clasificacion,$periodo,$dia,$turno,$valor){ 
             $replicas = array();
             $id = getReplica("restriccion_de_clasif_act");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO restriccion_de_clasif_act (id,clasificacion,periodo,dia,turno,valor) VALUES ($1,$2,$3,$4,$5,$6);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($clasificacion, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($valor, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_de_clasif_act WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_de_clasif_act WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_de_clasif_act WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_de_clasif_act", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_clasif_act SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_clasif_act SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_clasif_act SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_clasif_act SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_de_clasif_act WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_de_clasif_act.clasificacion = public.clasificacion_de_actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.periodo ON restriccion_de_clasif_act.periodo = public.periodo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_de_clasif_act.clasificacion = public.clasificacion_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.periodo ON restriccion_de_clasif_act.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_clasif_act.id,restriccion_de_clasif_act.clasificacion,restriccion_de_clasif_act.periodo,restriccion_de_clasif_act.dia,restriccion_de_clasif_act.turno,restriccion_de_clasif_act.valor,public.clasificacion_de_actividad.id,public.clasificacion_de_actividad.descripcion,public.clasificacion_de_actividad.duracion,public.clasificacion_de_actividad.creador,public.clasificacion_de_actividad.prioridad,public.clasificacion_de_actividad.gasto";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_de_clasif_act.clasificacion = public.clasificacion_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_clasif_act" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_clasif_act.id,restriccion_de_clasif_act.clasificacion,restriccion_de_clasif_act.periodo,restriccion_de_clasif_act.dia,restriccion_de_clasif_act.turno,restriccion_de_clasif_act.valor,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_clasif_act INNER JOIN public.periodo ON restriccion_de_clasif_act.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>