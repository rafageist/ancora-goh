<?php 
   class OtroRecursoParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: otro_recurso_para_actividad 
           * @desc: Work with table "otro_recurso_para_actividad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM otro_recurso_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($grupo_de_recursos,$prioridad,$otro_recurso){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO otro_recurso_para_actividad (grupo_de_recursos,prioridad,otro_recurso) VALUES ($1,$2,$3);", array(PartModel::sqlValue($grupo_de_recursos, "text"), PartModel::sqlValue($prioridad, "number"), PartModel::sqlValue($otro_recurso, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($grupo_de_recursos,$otro_recurso){ 
                  return $this->getAnythingParams("DELETE FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1 AND otro_recurso = $2;", array(PartModel::sqlValue($grupo_de_recursos, "text"), PartModel::sqlValue($otro_recurso, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM otro_recurso_para_actividad WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM otro_recurso_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM otro_recurso_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otro_recurso_para_actividad SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE otro_recurso_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otro_recurso_para_actividad SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE otro_recurso_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($grupo_de_recursos,$otro_recurso){ 
                  $r = $this->getAnythingParams("SELECT * FROM otro_recurso_para_actividad WHERE grupo_de_recursos = $1 AND otro_recurso = $2;", array(PartModel::sqlValue($grupo_de_recursos, "text"), PartModel::sqlValue($otro_recurso, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = public.otro_grupo_de_recursos_para_actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = public.otro_recurso.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = public.otro_grupo_de_recursos_para_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otro_recurso_para_actividad.grupo_de_recursos,otro_recurso_para_actividad.prioridad,otro_recurso_para_actividad.otro_recurso,public.otro_grupo_de_recursos_para_actividad.id,public.otro_grupo_de_recursos_para_actividad.plantilla,public.otro_grupo_de_recursos_para_actividad.prioridad,public.otro_grupo_de_recursos_para_actividad.necesarios,public.otro_grupo_de_recursos_para_actividad.imprescindibles,public.otro_grupo_de_recursos_para_actividad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = public.otro_grupo_de_recursos_para_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otro_recurso_para_actividad.grupo_de_recursos,otro_recurso_para_actividad.prioridad,otro_recurso_para_actividad.otro_recurso,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>