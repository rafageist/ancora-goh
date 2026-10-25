<?php 
   class AsignaturaModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: asignatura 
           * @desc: Work with table "asignatura" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "asignatura" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM asignatura; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "asignatura" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($descripcion,$especialidad,$nivel,$creador){ 
             $replicas = array();
             $id = getReplica("asignatura");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO asignatura (id,descripcion,especialidad,nivel,creador) VALUES ('$id',".(descripcion==null?'null':"'".$descripcion."'").",".(especialidad==null?'null':"'".$especialidad."'").",".(nivel==null?'null':"'".$nivel."'").",".(creador==null?'null':"'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "asignatura" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM asignatura WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'asignatura' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM asignatura WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'asignatura' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM asignatura", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'asignatura' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE asignatura SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'asignatura' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE asignatura SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "asignatura" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM asignatura WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'asignatura' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM asignatura;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'asignatura' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM asignatura WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'asignatura' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM asignatura LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'asignatura' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM asignatura WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN seguridad.usuario ON asignatura.creador = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.especialidad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.especialidad ON asignatura.especialidad = public.especialidad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.nivel" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithNivel($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.nivel ON asignatura.nivel = public.nivel.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN seguridad.usuario ON asignatura.creador = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.especialidad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.especialidad ON asignatura.especialidad = public.especialidad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.nivel by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.nivel ON asignatura.nivel = public.nivel.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["asignatura"]); 
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
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN seguridad.usuario ON asignatura.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.especialidad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["asignatura"]); 
                    $fieldsforaneas = explode($this->fieldnames["especialidad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.especialidad ON asignatura.especialidad = especialidad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "asignatura" and "public.nivel, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["asignatura"]); 
                    $fieldsforaneas = explode($this->fieldnames["nivel"]); 
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
               $r = $this->getAnything("SELECT $fields FROM asignatura INNER JOIN public.nivel ON asignatura.nivel = nivel.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>