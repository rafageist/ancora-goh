<?php 
   class RolDeUsuarioModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: seguridad.rol_de_usuario 
           * @desc: Work with table "seguridad_rol_de_usuario" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "seguridad.rol_de_usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM seguridad.rol_de_usuario; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "seguridad.rol_de_usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($usuario,$rol){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO seguridad.rol_de_usuario (usuario,rol) VALUES (".(usuario==null?'null':"'".$usuario."'").",".(rol==null?'null':"'".$rol."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "seguridad.rol_de_usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($usuario,$rol){ 
                  return $this->getAnything("DELETE FROM seguridad.rol_de_usuario WHERE  usuario = '$usuario'  AND  rol = '$rol' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'seguridad.rol_de_usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM seguridad.rol_de_usuario WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'seguridad.rol_de_usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM seguridad.rol_de_usuario", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.rol_de_usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.rol_de_usuario SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.rol_de_usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.rol_de_usuario SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "seguridad.rol_de_usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($usuario,$rol){ 
                  $r = $this->getAnything("SELECT * FROM seguridad.rol_de_usuario WHERE  usuario = '$usuario'  AND  rol = '$rol' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'seguridad.rol_de_usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'seguridad.rol_de_usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'seguridad.rol_de_usuario' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'seguridad.rol_de_usuario' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRol($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.rol ON seguridad.rol_de_usuario.rol = seguridad.rol.nombre ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.usuario ON seguridad.rol_de_usuario.usuario = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.rol by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRolBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.rol ON seguridad.rol_de_usuario.rol = seguridad.rol.nombre  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.usuario ON seguridad.rol_de_usuario.usuario = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.rol, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRolByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["seguridad.rol_de_usuario"]); 
                    $fieldsforaneas = explode($this->fieldnames["rol"]); 
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
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.rol ON seguridad.rol_de_usuario.rol = rol.nombre  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.rol_de_usuario" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["seguridad.rol_de_usuario"]); 
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
               $r = $this->getAnything("SELECT $fields FROM seguridad.rol_de_usuario INNER JOIN seguridad.usuario ON seguridad.rol_de_usuario.usuario = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>