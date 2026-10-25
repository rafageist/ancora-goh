<?php 
   class RestriccionPorActividadDeBrigadaModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_por_actividad_de_brigada 
           * @desc: Work with table "restriccion_por_actividad_de_brigada" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_por_actividad_de_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_por_actividad_de_brigada; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_por_actividad_de_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$actividad){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO restriccion_por_actividad_de_brigada (restriccion_nominal,actividad) VALUES ($1,$2);", array(PartModel::sqlValue($restriccion_nominal, "text"), PartModel::sqlValue($actividad, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_por_actividad_de_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_por_actividad_de_brigada WHERE restriccion_nominal = $1;", array(PartModel::sqlValue($restriccion_nominal, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_por_actividad_de_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_por_actividad_de_brigada WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_por_actividad_de_brigada WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_por_actividad_de_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_por_actividad_de_brigada", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_por_actividad_de_brigada SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_por_actividad_de_brigada SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_por_actividad_de_brigada SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_por_actividad_de_brigada SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_por_actividad_de_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_por_actividad_de_brigada WHERE restriccion_nominal = $1;", array(PartModel::sqlValue($restriccion_nominal, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_por_actividad_de_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_por_actividad_de_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_por_actividad_de_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_por_actividad_de_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.actividad ON restriccion_por_actividad_de_brigada.actividad = public.actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.restriccion_de_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.restriccion_de_brigada ON restriccion_por_actividad_de_brigada.restriccion_nominal = public.restriccion_de_brigada.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.actividad ON restriccion_por_actividad_de_brigada.actividad = public.actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.restriccion_de_brigada by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeBrigadaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.restriccion_de_brigada ON restriccion_por_actividad_de_brigada.restriccion_nominal = public.restriccion_de_brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_por_actividad_de_brigada.restriccion_nominal,restriccion_por_actividad_de_brigada.actividad,public.actividad.id,public.actividad.plantilla,public.actividad.grupo,public.actividad.profesor,public.actividad.lugar,public.actividad.dia,public.actividad.turno,public.actividad.momento_de_colocacion,public.actividad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.actividad ON restriccion_por_actividad_de_brigada.actividad = public.actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_brigada" and "public.restriccion_de_brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_por_actividad_de_brigada.restriccion_nominal,restriccion_por_actividad_de_brigada.actividad,public.restriccion_de_brigada.id,public.restriccion_de_brigada.brigada,public.restriccion_de_brigada.periodo,public.restriccion_de_brigada.dia,public.restriccion_de_brigada.turno,public.restriccion_de_brigada.valor,public.restriccion_de_brigada.por_actividad,public.restriccion_de_brigada.actividad";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_por_actividad_de_brigada INNER JOIN public.restriccion_de_brigada ON restriccion_por_actividad_de_brigada.restriccion_nominal = public.restriccion_de_brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>