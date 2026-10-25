<?php 
   class LugarModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: lugar 
           * @desc: Work with table "lugar" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM lugar; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($nombre,$capacidad,$creador){ 
             $replicas = array();
             $id = getReplica("lugar");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO lugar (id,nombre,capacidad,creador) VALUES ('$id',".(nombre==null?'null':"'".$nombre."'").",".(capacidad==null?'null':$capacidad).",".(creador==null?'null':"'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM lugar WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM lugar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM lugar", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE lugar SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE lugar SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM lugar WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar INNER JOIN seguridad.usuario ON lugar.creador = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar INNER JOIN seguridad.usuario ON lugar.creador = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["lugar"]); 
                    $fieldsforaneas = explode($this->fieldnames["usuario"]); 
                    $ff = "";
                    $i = 0;
                    foreach ($fieldstabla as $f) {
                        $i++; 
                        if ($i>1) $ff = $ff.",";
                        $ff = $ff.$f;
                    }
                    foreach ($fieldsforaneas as $f) {
                        $i++; 
                        if ($i>1) $ff = $ff.",";
                        $ff = $ff.$f;
                    }
                    $fields=$ff;
               }
               $r = $this->getAnything("SELECT $fields FROM lugar INNER JOIN seguridad.usuario ON lugar.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>