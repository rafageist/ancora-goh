<?php 
   class OtroGrupoDeRecursosParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: otro_grupo_de_recursos_para_actividad 
           * @desc: Work with table "otro_grupo_de_recursos_para_actividad" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM otro_grupo_de_recursos_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->imprescindibles)) {if ($obj->imprescindibles == "f") $obj->imprescindibles = false; else $obj->imprescindibles = true; }
             } else {
                  if (isset($obj["imprescindibles"])) {if ($obj["imprescindibles"] == "f") $obj["imprescindibles"] = false; else $obj["imprescindibles"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($plantilla,$prioridad,$necesarios,$imprescindibles,$creador){ 
             $replicas = array();
             $id = getReplica("otro_grupo_de_recursos_para_actividad");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO otro_grupo_de_recursos_para_actividad (id,plantilla,prioridad,necesarios,imprescindibles,creador) VALUES ($1,$2,$3,$4,$5,$6);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($plantilla, "text"), PartModel::sqlValue($prioridad, "number"), PartModel::sqlValue($necesarios, "number"), PartModel::sqlValue($imprescindibles, "bool"), PartModel::sqlValue($creador, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM otro_grupo_de_recursos_para_actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM otro_grupo_de_recursos_para_actividad WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM otro_grupo_de_recursos_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM otro_grupo_de_recursos_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE otro_grupo_de_recursos_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otro_grupo_de_recursos_para_actividad SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE otro_grupo_de_recursos_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = seguridad.usuario.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = public.plantilla_de_actividad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = seguridad.usuario.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = public.plantilla_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otro_grupo_de_recursos_para_actividad.id,otro_grupo_de_recursos_para_actividad.plantilla,otro_grupo_de_recursos_para_actividad.prioridad,otro_grupo_de_recursos_para_actividad.necesarios,otro_grupo_de_recursos_para_actividad.imprescindibles,otro_grupo_de_recursos_para_actividad.creador,seguridad.usuario.id,seguridad.usuario.nombre,seguridad.usuario.info,seguridad.usuario.correo,seguridad.usuario.momento_ingreso,seguridad.usuario.nick,seguridad.usuario.contrasena";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = seguridad.usuario.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otro_grupo_de_recursos_para_actividad.id,otro_grupo_de_recursos_para_actividad.plantilla,otro_grupo_de_recursos_para_actividad.prioridad,otro_grupo_de_recursos_para_actividad.necesarios,otro_grupo_de_recursos_para_actividad.imprescindibles,otro_grupo_de_recursos_para_actividad.creador,public.plantilla_de_actividad.id,public.plantilla_de_actividad.desglose,public.plantilla_de_actividad.orden,public.plantilla_de_actividad.clasificacion,public.plantilla_de_actividad.exige_profesor,public.plantilla_de_actividad.exige_lugar";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = public.plantilla_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>