<?php 
   class ProfesorParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: profesor_para_actividad 
           * @desc: Work with table "profesor_para_actividad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM profesor_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($plantilla,$profesor,$prioridad){ 
             $replicas = array();
             $id = getReplica("profesor_para_actividad");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO profesor_para_actividad (id,plantilla,profesor,prioridad) VALUES ('$id',".(plantilla==null?'null':"'".$plantilla."'").",".(profesor==null?'null':"'".$profesor."'").",".(prioridad==null?'null':$prioridad).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM profesor_para_actividad WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'profesor_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM profesor_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'profesor_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM profesor_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE profesor_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'profesor_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE profesor_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "profesor_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM profesor_para_actividad WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'profesor_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_actividad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'profesor_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'profesor_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_actividad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'profesor_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM profesor_para_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.plantilla_de_actividad ON profesor_para_actividad.plantilla = public.plantilla_de_actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.profesor" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.profesor ON profesor_para_actividad.profesor = public.profesor.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.plantilla_de_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.plantilla_de_actividad ON profesor_para_actividad.plantilla = public.plantilla_de_actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.profesor ON profesor_para_actividad.profesor = public.profesor.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.plantilla_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["profesor_para_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["plantilla_de_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.plantilla_de_actividad ON profesor_para_actividad.plantilla = plantilla_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "profesor_para_actividad" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["profesor_para_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["profesor"]); 
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
               $r = $this->getAnything("SELECT $fields FROM profesor_para_actividad INNER JOIN public.profesor ON profesor_para_actividad.profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>