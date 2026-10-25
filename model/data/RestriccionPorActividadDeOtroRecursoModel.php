<?php 
   class RestriccionPorActividadDeOtroRecursoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_por_actividad_de_otro_recurso 
           * @desc: Work with table "restriccion_por_actividad_de_otro_recurso" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_por_actividad_de_otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_por_actividad_de_otro_recurso; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_por_actividad_de_otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$actividad){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO restriccion_por_actividad_de_otro_recurso (restriccion_nominal,actividad) VALUES ($1,$2);", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($actividad, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_por_actividad_de_otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_por_actividad_de_otro_recurso WHERE restriccion_nominal = $1;", array(PartModel::sqlValue($restriccion_nominal, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_por_actividad_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_por_actividad_de_otro_recurso WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_por_actividad_de_otro_recurso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_por_actividad_de_otro_recurso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_por_actividad_de_otro_recurso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_otro_recurso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_por_actividad_de_otro_recurso SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_por_actividad_de_otro_recurso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_por_actividad_de_otro_recurso SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_por_actividad_de_otro_recurso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_por_actividad_de_otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_por_actividad_de_otro_recurso WHERE restriccion_nominal = $1;", array(PartModel::sqlValue($restriccion_nominal, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_por_actividad_de_otro_recurso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_por_actividad_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_por_actividad_de_otro_recurso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_por_actividad_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_por_actividad_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.actividad ON restriccion_por_actividad_de_otro_recurso.actividad = public.actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.restriccion_de_otro_recurso by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_por_actividad_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.actividad ON restriccion_por_actividad_de_otro_recurso.actividad = public.actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.restriccion_de_otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_por_actividad_de_otro_recurso.restriccion_nominal,restriccion_por_actividad_de_otro_recurso.actividad,public.restriccion_de_otro_recurso.id,public.restriccion_de_otro_recurso.otro_recurso,public.restriccion_de_otro_recurso.periodo,public.restriccion_de_otro_recurso.dia,public.restriccion_de_otro_recurso.turno,public.restriccion_de_otro_recurso.valor,public.restriccion_de_otro_recurso.por_actividad,public.restriccion_de_otro_recurso.actividad";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.restriccion_de_otro_recurso ON restriccion_por_actividad_de_otro_recurso.restriccion_nominal = public.restriccion_de_otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_otro_recurso" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_por_actividad_de_otro_recurso.restriccion_nominal,restriccion_por_actividad_de_otro_recurso.actividad,public.actividad.id,public.actividad.plantilla,public.actividad.grupo,public.actividad.profesor,public.actividad.lugar,public.actividad.dia,public.actividad.turno,public.actividad.momento_de_colocacion,public.actividad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_otro_recurso INNER JOIN public.actividad ON restriccion_por_actividad_de_otro_recurso.actividad = public.actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>