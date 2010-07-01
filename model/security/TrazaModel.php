<?php 
   class TrazaModel extends PartModel { 
 
          public function TrazaModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: seguridad.traza 
           * @desc: Work with table "seguridad_traza" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "seguridad.traza" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM seguridad.traza; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "seguridad.traza" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($fecha,$usuario,$accion,$ip){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO seguridad.traza (fecha,usuario,accion,ip) VALUES (".(fecha==null?'null':"'".$fecha."'").",".(usuario==null?'null':"'".$usuario."'").",".(accion==null?'null':"'".$accion."'").",".(ip==null?'null':"'".$ip."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove 'seguridad.traza' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM seguridad.traza WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'seguridad.traza' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM seguridad.traza", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.traza' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.traza SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'seguridad.traza' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE seguridad.traza SET $set WHERE $where;");
          } 
          /** 
            * @name: getAll 
            * @desc: Get all 'seguridad.traza' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.traza;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'seguridad.traza' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.traza WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'seguridad.traza' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.traza LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'seguridad.traza' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM seguridad.traza WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.traza" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.traza INNER JOIN seguridad.usuario ON seguridad.traza.usuario = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.traza" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM seguridad.traza INNER JOIN seguridad.usuario ON seguridad.traza.usuario = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "seguridad.traza" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["seguridad.traza"]); 
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
               $r = $this->getAnything("SELECT $fields FROM seguridad.traza INNER JOIN seguridad.usuario ON seguridad.traza.usuario = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>