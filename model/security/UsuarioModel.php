<?php 
   class UsuarioModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: seguridad.usuario 
           * @desc: Work with table "seguridad_usuario" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM seguridad.usuario; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($nombre,$info,$correo,$momento_ingreso,$nick,$contrasena){ 
             $replicas = array();
             $id = getReplica("seguridad.usuario");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO seguridad.usuario (id,nombre,info,correo,momento_ingreso,nick,contrasena) VALUES ('$id',".(nombre==null?'null':"'".$nombre."'").",".(info==null?'null':"'".$info."'").",".(correo==null?'null':"'".$correo."'").",".(momento_ingreso==null?'null':$momento_ingreso).",".(nick==null?'null':"'".$nick."'").",'".md5($contrasena)."');"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM seguridad.usuario WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'seguridad.usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM seguridad.usuario WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'seguridad.usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM seguridad.usuario", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.usuario SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.usuario SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM seguridad.usuario WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'seguridad.usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.usuario;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'seguridad.usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.usuario WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'seguridad.usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.usuario LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'seguridad.usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.usuario WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
    } 
?>