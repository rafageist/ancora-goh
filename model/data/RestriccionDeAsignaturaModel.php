<?php 
   class RestriccionDeAsignaturaModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_de_asignatura 
           * @desc: Work with table "restriccion_de_asignatura" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_de_asignatura" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_de_asignatura; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_de_asignatura" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($asignatura,$periodo,$dia,$turno,$valor){ 
             $replicas = array();
             $id = getReplica("restriccion_de_asignatura");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO restriccion_de_asignatura (id,asignatura,periodo,dia,turno,valor) VALUES ($1,$2,$3,$4,$5,$6);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($asignatura, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($valor, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_de_asignatura" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_de_asignatura WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_de_asignatura' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_de_asignatura WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_de_asignatura WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_de_asignatura' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_de_asignatura", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_asignatura' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_asignatura SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_asignatura SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_asignatura' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_de_asignatura SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_de_asignatura SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_de_asignatura" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_de_asignatura WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_de_asignatura' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_de_asignatura' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_de_asignatura' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_de_asignatura' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.asignatura" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignatura($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.asignatura ON restriccion_de_asignatura.asignatura = public.asignatura.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.periodo ON restriccion_de_asignatura.periodo = public.periodo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.asignatura by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.asignatura ON restriccion_de_asignatura.asignatura = public.asignatura.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.periodo ON restriccion_de_asignatura.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.asignatura, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_asignatura.id,restriccion_de_asignatura.asignatura,restriccion_de_asignatura.periodo,restriccion_de_asignatura.dia,restriccion_de_asignatura.turno,restriccion_de_asignatura.valor,public.asignatura.id,public.asignatura.descripcion,public.asignatura.especialidad,public.asignatura.nivel,public.asignatura.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.asignatura ON restriccion_de_asignatura.asignatura = public.asignatura.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_asignatura" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_de_asignatura.id,restriccion_de_asignatura.asignatura,restriccion_de_asignatura.periodo,restriccion_de_asignatura.dia,restriccion_de_asignatura.turno,restriccion_de_asignatura.valor,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_de_asignatura INNER JOIN public.periodo ON restriccion_de_asignatura.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>