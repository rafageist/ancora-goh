<?php 
   class PermisoModel extends PartModel { 
 
          public function PermisoModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: seguridad.permiso 
           * @desc: Work with table "seguridad_permiso" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "seguridad.permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM seguridad.permiso; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "seguridad.permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($permiso,$rol){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO seguridad.permiso (permiso,rol) VALUES (".(permiso==null?'null':"'".$permiso."'").",".(rol==null?'null':"'".$rol."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "seguridad.permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($permiso,$rol){ 
                  return $this->getAnything("DELETE FROM seguridad.permiso WHERE  permiso = '$permiso'  AND  rol = '$rol' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'seguridad.permiso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM seguridad.permiso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'seguridad.permiso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM seguridad.permiso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.permiso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.permiso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.permiso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.permiso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "seguridad.permiso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($permiso,$rol){ 
                  $r = $this->getAnything("SELECT * FROM seguridad.permiso WHERE  permiso = '$permiso'  AND  rol = '$rol' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'seguridad.permiso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.permiso;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'seguridad.permiso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.permiso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'seguridad.permiso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.permiso LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'seguridad.permiso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.permiso WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.permiso" and "seguridad.rol" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRol($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.permiso INNER JOIN seguridad.rol ON seguridad.permiso.rol = seguridad.rol.nombre ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.permiso" and "seguridad.rol by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRolBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.permiso INNER JOIN seguridad.rol ON seguridad.permiso.rol = seguridad.rol.nombre  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.permiso" and "seguridad.rol, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadRolByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["seguridad.permiso"]); 
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
               $r = $this->getAnything("SELECT $fields FROM seguridad.permiso INNER JOIN seguridad.rol ON seguridad.permiso.rol = rol.nombre  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>