<?php 
   class RolModel extends PartModel { 
 
          public function RolModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: seguridad.rol 
           * @desc: Work with table "seguridad_rol" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM seguridad.rol; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($nombre,$descripcion){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO seguridad.rol (nombre,descripcion) VALUES (".(nombre==null?'null':"'".$nombre."'").",".(descripcion==null?'null':"'".$descripcion."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($nombre){ 
                  return $this->getAnything("DELETE FROM seguridad.rol WHERE  nombre = '$nombre' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'seguridad.rol' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM seguridad.rol WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'seguridad.rol' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM seguridad.rol", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.rol' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.rol SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.rol' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.rol SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($nombre){ 
                  $r = $this->getAnything("SELECT * FROM seguridad.rol WHERE  nombre = '$nombre' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'seguridad.rol' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'seguridad.rol' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'seguridad.rol' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'seguridad.rol' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
    } 
?>