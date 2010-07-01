<?php 
   class ProfesorParaGrupoModel extends PartModel { 
 
          public function ProfesorParaGrupoModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: profesor_para_grupo 
           * @desc: Work with table "profesor_para_grupo" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM profesor_para_grupo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($profesor_para_actividad,$grupo){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO profesor_para_grupo (profesor_para_actividad,grupo) VALUES (".(profesor_para_actividad==null?'null':"'".$profesor_para_actividad."'").",".(grupo==null?'null':"'".$grupo."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($profesor_para_actividad,$grupo){ 
                  return $this->getAnything("DELETE FROM profesor_para_grupo WHERE  profesor_para_actividad = '$profesor_para_actividad'  AND  grupo = '$grupo' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM profesor_para_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM profesor_para_grupo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE profesor_para_grupo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE profesor_para_grupo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "profesor_para_grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($profesor_para_actividad,$grupo){ 
                  $r = $this->getAnything("SELECT * FROM profesor_para_grupo WHERE  profesor_para_actividad = '$profesor_para_actividad'  AND  grupo = '$grupo' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_grupo;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'profesor_para_grupo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_grupo LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'profesor_para_grupo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_grupo WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = public.grupo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = public.profesor_para_actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = public.grupo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = public.profesor_para_actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["profesor_para_grupo"]); 
                    $fieldsforaneas = explode($this->fieldnames["grupo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.grupo ON profesor_para_grupo.grupo = grupo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_grupo" and "public.profesor_para_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorParaActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["profesor_para_grupo"]); 
                    $fieldsforaneas = explode($this->fieldnames["profesor_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM profesor_para_grupo INNER JOIN public.profesor_para_actividad ON profesor_para_grupo.profesor_para_actividad = profesor_para_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>