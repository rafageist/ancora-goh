<?php 
   class CancelarGeneradorModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: generador.cancelar 
           * @desc: Work with table "generador_cancelar" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM generador.cancelar; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($especialidad,$nivel,$periodo){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO generador.cancelar (especialidad,nivel,periodo) VALUES ($1,$2,$3);", array(PartModel::sqlValue($especialidad, "text"), PartModel::sqlValue($nivel, "text"), PartModel::sqlValue($periodo, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($especialidad,$nivel,$periodo){ 
                  return $this->getAnythingParams("DELETE FROM generador.cancelar WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;", array(PartModel::sqlValue($especialidad, "text"), PartModel::sqlValue($nivel, "text"), PartModel::sqlValue($periodo, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM generador.cancelar WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM generador.cancelar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM generador.cancelar", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE generador.cancelar SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE generador.cancelar SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE generador.cancelar SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE generador.cancelar SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($especialidad,$nivel,$periodo){ 
                  $r = $this->getAnythingParams("SELECT * FROM generador.cancelar WHERE especialidad = $1 AND nivel = $2 AND periodo = $3;", array(PartModel::sqlValue($especialidad, "text"), PartModel::sqlValue($nivel, "text"), PartModel::sqlValue($periodo, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM generador.cancelar;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM generador.cancelar WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM generador.cancelar LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM generador.cancelar WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
    } 
?>