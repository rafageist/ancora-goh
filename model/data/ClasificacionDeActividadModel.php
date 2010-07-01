<?php 
   class ClasificacionDeActividadModel extends PartModel { 
 
          public function ClasificacionDeActividadModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: clasificacion_de_actividad 
           * @desc: Work with table "clasificacion_de_actividad" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM clasificacion_de_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($descripcion,$duracion,$creador,$prioridad,$gasto){ 
             $replicas = array();
             $id = getReplica("clasificacion_de_actividad");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO clasificacion_de_actividad (id,descripcion,duracion,creador,prioridad,gasto) VALUES ('$id',".(descripcion==null?'null':"'".$descripcion."'").",".(duracion==null?'null':$duracion).",".(creador==null?'null':"'".$creador."'").",".(prioridad==null?'null':$prioridad).",".(gasto==null?'null':$gasto).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM clasificacion_de_actividad WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'clasificacion_de_actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM clasificacion_de_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'clasificacion_de_actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM clasificacion_de_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'clasificacion_de_actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE clasificacion_de_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'clasificacion_de_actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE clasificacion_de_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM clasificacion_de_actividad WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'clasificacion_de_actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM clasificacion_de_actividad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'clasificacion_de_actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM clasificacion_de_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'clasificacion_de_actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM clasificacion_de_actividad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'clasificacion_de_actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM clasificacion_de_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "clasificacion_de_actividad" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM clasificacion_de_actividad INNER JOIN seguridad.usuario ON clasificacion_de_actividad.creador = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "clasificacion_de_actividad" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM clasificacion_de_actividad INNER JOIN seguridad.usuario ON clasificacion_de_actividad.creador = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "clasificacion_de_actividad" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["clasificacion_de_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM clasificacion_de_actividad INNER JOIN seguridad.usuario ON clasificacion_de_actividad.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>