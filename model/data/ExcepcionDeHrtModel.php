<?php 
   class ExcepcionDeHrtModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: excepcion_de_hrt 
           * @desc: Work with table "excepcion_de_hrt" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM excepcion_de_hrt; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($herencia,$periodo,$dia,$turno){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO excepcion_de_hrt (herencia,periodo,dia,turno) VALUES ($1,$2,$3,$4);", array(PartModel::sqlValue($herencia, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($herencia,$periodo,$dia,$turno){ 
                  return $this->getAnythingParams("DELETE FROM excepcion_de_hrt WHERE herencia = $1 AND periodo = $2 AND dia = $3 AND turno = $4;", array(PartModel::sqlValue($herencia, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM excepcion_de_hrt WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM excepcion_de_hrt WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM excepcion_de_hrt", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE excepcion_de_hrt SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE excepcion_de_hrt SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE excepcion_de_hrt SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE excepcion_de_hrt SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($herencia,$periodo,$dia,$turno){ 
                  $r = $this->getAnythingParams("SELECT * FROM excepcion_de_hrt WHERE herencia = $1 AND periodo = $2 AND dia = $3 AND turno = $4;", array(PartModel::sqlValue($herencia, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestricciones($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = public.herencia_de_restricciones.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = public.periodo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestriccionesBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = public.herencia_de_restricciones.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestriccionesByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "excepcion_de_hrt.herencia,excepcion_de_hrt.periodo,excepcion_de_hrt.dia,excepcion_de_hrt.turno,public.herencia_de_restricciones.id,public.herencia_de_restricciones.planificacion,public.herencia_de_restricciones.activa,public.herencia_de_restricciones.hijo_brigada,public.herencia_de_restricciones.hijo_profesor,public.herencia_de_restricciones.hijo_lugar,public.herencia_de_restricciones.hijo_otro_recurso,public.herencia_de_restricciones.padre_brigada,public.herencia_de_restricciones.padre_profesor,public.herencia_de_restricciones.padre_lugar,public.herencia_de_restricciones.padre_otro_recurso";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = public.herencia_de_restricciones.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "excepcion_de_hrt.herencia,excepcion_de_hrt.periodo,excepcion_de_hrt.dia,excepcion_de_hrt.turno,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>