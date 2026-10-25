<?php 
   class HerenciaDeRestriccionesModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: herencia_de_restricciones 
           * @desc: Work with table "herencia_de_restricciones" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM herencia_de_restricciones; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->activa)) {if ($obj->activa == "f") $obj->activa = false; else $obj->activa = true; }
             } else {
                  if (isset($obj["activa"])) {if ($obj["activa"] == "f") $obj["activa"] = false; else $obj["activa"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso){ 
             $replicas = array();
             $id = getReplica("herencia_de_restricciones");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO herencia_de_restricciones (id,planificacion,activa,hijo_brigada,hijo_profesor,hijo_lugar,hijo_otro_recurso,padre_brigada,padre_profesor,padre_lugar,padre_otro_recurso) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($planificacion, "text"), PartModel::sqlValue($activa, "bool"), PartModel::sqlValue($hijo_brigada, "text"), PartModel::sqlValue($hijo_profesor, "text"), PartModel::sqlValue($hijo_lugar, "text"), PartModel::sqlValue($hijo_otro_recurso, "text"), PartModel::sqlValue($padre_brigada, "text"), PartModel::sqlValue($padre_profesor, "text"), PartModel::sqlValue($padre_lugar, "text"), PartModel::sqlValue($padre_otro_recurso, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM herencia_de_restricciones WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM herencia_de_restricciones WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM herencia_de_restricciones WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM herencia_de_restricciones", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE herencia_de_restricciones SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE herencia_de_restricciones SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE herencia_de_restricciones SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE herencia_de_restricciones SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM herencia_de_restricciones WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = public.brigada.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = public.lugar.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = public.profesor.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = public.brigada.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = public.lugar.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = public.profesor.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacion($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = public.planificacion.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = public.brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = public.brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = public.planificacion.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.brigada.id,public.brigada.descripcion,public.brigada.especialidad,public.brigada.nivel,public.brigada.matricula,public.brigada.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = public.brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.brigada.id,public.brigada.descripcion,public.brigada.especialidad,public.brigada.nivel,public.brigada.matricula,public.brigada.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = public.brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "herencia_de_restricciones.id,herencia_de_restricciones.planificacion,herencia_de_restricciones.activa,herencia_de_restricciones.hijo_brigada,herencia_de_restricciones.hijo_profesor,herencia_de_restricciones.hijo_lugar,herencia_de_restricciones.hijo_otro_recurso,herencia_de_restricciones.padre_brigada,herencia_de_restricciones.padre_profesor,herencia_de_restricciones.padre_lugar,herencia_de_restricciones.padre_otro_recurso,public.planificacion.id,public.planificacion.descripcion,public.planificacion.fecha_inicial,public.planificacion.cant_dias,public.planificacion.activa,public.planificacion.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = public.planificacion.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>