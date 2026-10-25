<?php 
   class BrigadaDeGrupoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: brigada_de_grupo 
           * @desc: Work with table "brigada_de_grupo" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM brigada_de_grupo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($brigada,$grupo){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO brigada_de_grupo (brigada,grupo) VALUES ($1,$2);", array(PartModel::sqlValue($brigada, "text"), PartModel::sqlValue($grupo, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($brigada,$grupo){ 
                  return $this->getAnythingParams("DELETE FROM brigada_de_grupo WHERE brigada = $1 AND grupo = $2;", array(PartModel::sqlValue($brigada, "text"), PartModel::sqlValue($grupo, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM brigada_de_grupo WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM brigada_de_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM brigada_de_grupo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE brigada_de_grupo SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE brigada_de_grupo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE brigada_de_grupo SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE brigada_de_grupo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($brigada,$grupo){ 
                  $r = $this->getAnythingParams("SELECT * FROM brigada_de_grupo WHERE brigada = $1 AND grupo = $2;", array(PartModel::sqlValue($brigada, "text"), PartModel::sqlValue($grupo, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = public.brigada.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = public.grupo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = public.brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = public.grupo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "brigada_de_grupo.brigada,brigada_de_grupo.grupo,public.brigada.id,public.brigada.descripcion,public.brigada.especialidad,public.brigada.nivel,public.brigada.matricula,public.brigada.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = public.brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "brigada_de_grupo.brigada,brigada_de_grupo.grupo,public.grupo.id,public.grupo.clasificacion,public.grupo.descripcion,public.grupo.especialidad,public.grupo.nivel,public.grupo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = public.grupo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>