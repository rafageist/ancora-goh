<?php 
   class ZprioriModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: zpriori 
           * @desc: Work with table "zpriori" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM zpriori; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($clasificacion,$periodo,$dia,$turno,$valor){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO zpriori (clasificacion,periodo,dia,turno,valor) VALUES ($1,$2,$3,$4,$5);", array(PartModel::sqlValue($clasificacion, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($valor, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($clasificacion,$periodo,$dia,$turno){ 
                  return $this->getAnythingParams("DELETE FROM zpriori WHERE clasificacion = $1 AND periodo = $2 AND dia = $3 AND turno = $4;", array(PartModel::sqlValue($clasificacion, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM zpriori WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM zpriori WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM zpriori", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE zpriori SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE zpriori SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE zpriori SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE zpriori SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($clasificacion,$periodo,$dia,$turno){ 
                  $r = $this->getAnythingParams("SELECT * FROM zpriori WHERE clasificacion = $1 AND periodo = $2 AND dia = $3 AND turno = $4;", array(PartModel::sqlValue($clasificacion, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = public.clasificacion_de_actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = public.periodo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = public.clasificacion_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "zpriori.clasificacion,zpriori.periodo,zpriori.dia,zpriori.turno,zpriori.valor,public.clasificacion_de_actividad.id,public.clasificacion_de_actividad.descripcion,public.clasificacion_de_actividad.duracion,public.clasificacion_de_actividad.creador,public.clasificacion_de_actividad.prioridad,public.clasificacion_de_actividad.gasto";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = public.clasificacion_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "zpriori.clasificacion,zpriori.periodo,zpriori.dia,zpriori.turno,zpriori.valor,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>