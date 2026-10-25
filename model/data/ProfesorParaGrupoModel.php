<?php 
   class ProfesorParaGrupoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: profesor_para_grupo 
           * @desc: Work with table "profesor_para_grupo" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM profesor_para_grupo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($profesor_para_actividad,$grupo){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO profesor_para_grupo (profesor_para_actividad,grupo) VALUES ($1,$2);", array(PartModel::sqlValue($profesor_para_actividad, "text"), PartModel::sqlValue($grupo, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($profesor_para_actividad,$grupo){ 
                  return $this->getAnythingParams("DELETE FROM profesor_para_grupo WHERE profesor_para_actividad = $1 AND grupo = $2;", array(PartModel::sqlValue($profesor_para_actividad, "text"), PartModel::sqlValue($grupo, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM profesor_para_grupo WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM profesor_para_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM profesor_para_grupo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE profesor_para_grupo SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE profesor_para_grupo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE profesor_para_grupo SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE profesor_para_grupo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($profesor_para_actividad,$grupo){ 
                  $r = $this->getAnythingParams("SELECT * FROM profesor_para_grupo WHERE profesor_para_actividad = $1 AND grupo = $2;", array(PartModel::sqlValue($profesor_para_actividad, "text"), PartModel::sqlValue($grupo, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = public.grupo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = public.profesor_para_actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = public.grupo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = public.profesor_para_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "profesor_para_grupo.profesor_para_actividad,profesor_para_grupo.grupo,public.grupo.id,public.grupo.clasificacion,public.grupo.descripcion,public.grupo.especialidad,public.grupo.nivel,public.grupo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = public.grupo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "profesor_para_grupo.profesor_para_actividad,profesor_para_grupo.grupo,public.profesor_para_actividad.id,public.profesor_para_actividad.plantilla,public.profesor_para_actividad.profesor,public.profesor_para_actividad.prioridad";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = public.profesor_para_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>