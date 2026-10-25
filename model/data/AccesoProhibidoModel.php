<?php 
   class AccesoProhibidoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: acceso_prohibido 
           * @desc: Work with table "acceso_prohibido" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM acceso_prohibido; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($grupo,$lugar){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO acceso_prohibido (grupo,lugar) VALUES ($1,$2);", array(PartModel::sqlValue($grupo, "text"), PartModel::sqlValue($lugar, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($grupo,$lugar){ 
                  return $this->getAnythingParams("DELETE FROM acceso_prohibido WHERE grupo = $1 AND lugar = $2;", array(PartModel::sqlValue($grupo, "text"), PartModel::sqlValue($lugar, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM acceso_prohibido WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM acceso_prohibido WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM acceso_prohibido", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE acceso_prohibido SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE acceso_prohibido SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE acceso_prohibido SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE acceso_prohibido SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($grupo,$lugar){ 
                  $r = $this->getAnythingParams("SELECT * FROM acceso_prohibido WHERE grupo = $1 AND lugar = $2;", array(PartModel::sqlValue($grupo, "text"), PartModel::sqlValue($lugar, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = public.grupo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = public.lugar.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = public.grupo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "acceso_prohibido.grupo,acceso_prohibido.lugar,public.grupo.id,public.grupo.clasificacion,public.grupo.descripcion,public.grupo.especialidad,public.grupo.nivel,public.grupo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = public.grupo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "acceso_prohibido.grupo,acceso_prohibido.lugar,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>