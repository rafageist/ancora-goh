<?php 
   class CancelarGeneradorModel extends PartModel { 
 
          public function CancelarGeneradorModel($db){ 
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
             $r = $this->getAnything("INSERT INTO generador.cancelar (especialidad,nivel,periodo) VALUES (".(especialidad==null?'null':"'".$especialidad."'").",".(nivel==null?'null':"'".$nivel."'").",".(periodo==null?'null':"'".$periodo."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($especialidad,$nivel,$periodo){ 
                  return $this->getAnything("DELETE FROM generador.cancelar WHERE  especialidad = '$especialidad'  AND  nivel = '$nivel'  AND  periodo = '$periodo' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
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
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE generador.cancelar SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE generador.cancelar SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "generador.cancelar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($especialidad,$nivel,$periodo){ 
                  $r = $this->getAnything("SELECT * FROM generador.cancelar WHERE  especialidad = '$especialidad'  AND  nivel = '$nivel'  AND  periodo = '$periodo' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM generador.cancelar;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM generador.cancelar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'generador.cancelar' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM generador.cancelar LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'generador.cancelar' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM generador.cancelar WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
    } 
?>