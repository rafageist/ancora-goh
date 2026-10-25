<?php 
   class RestriccionPorActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_por_actividad 
           * @desc: Work with table "restriccion_por_actividad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_por_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_por_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_por_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$actividad){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO restriccion_por_actividad (restriccion_nominal,actividad) VALUES (".(restriccion_nominal==null?'null':"'".$restriccion_nominal."'").",".(actividad==null?'null':"'".$actividad."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_por_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal,$actividad){ 
                  return $this->getAnything("DELETE FROM restriccion_por_actividad WHERE  restriccion_nominal = '$restriccion_nominal'  AND  actividad = '$actividad' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_por_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM restriccion_por_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_por_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_por_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_por_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_por_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_por_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal,$actividad){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_por_actividad WHERE  restriccion_nominal = '$restriccion_nominal'  AND  actividad = '$actividad' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_por_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_por_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_por_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_por_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
    } 
?>