<?php 
   class PlantillaDeActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: plantilla_de_actividad 
           * @desc: Work with table "plantilla_de_actividad" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM plantilla_de_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->exige_profesor)) {if ($obj->exige_profesor == "f") $obj->exige_profesor = false; else $obj->exige_profesor = true; }
                  if (isset($obj->exige_lugar)) {if ($obj->exige_lugar == "f") $obj->exige_lugar = false; else $obj->exige_lugar = true; }
             } else {
                  if (isset($obj["exige_profesor"])) {if ($obj["exige_profesor"] == "f") $obj["exige_profesor"] = false; else $obj["exige_profesor"] = true; }
                  if (isset($obj["exige_lugar"])) {if ($obj["exige_lugar"] == "f") $obj["exige_lugar"] = false; else $obj["exige_lugar"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar){ 
             $replicas = array();
             $id = getReplica("plantilla_de_actividad");
             $replicas["id"] = $id;
               if ($exige_profesor == true || $exige_profesor == 1 || $exige_profesor == "t") $exige_profesor = "true"; 
               else if ($exige_profesor== true || $exige_profesor == 0 || $exige_profesor == "f") $exige_profesor = "false"; 
               if ($exige_lugar == true || $exige_lugar == 1 || $exige_lugar == "t") $exige_lugar = "true"; 
               else if ($exige_lugar== true || $exige_lugar == 0 || $exige_lugar == "f") $exige_lugar = "false"; 
             $r = $this->getAnything("INSERT INTO plantilla_de_actividad (id,desglose,orden,clasificacion,exige_profesor,exige_lugar) VALUES ('$id',".("'".$desglose."'").",".($orden).",".("'".$clasificacion."'").",".($exige_profesor).",".($exige_lugar).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM plantilla_de_actividad WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM plantilla_de_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM plantilla_de_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE plantilla_de_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE plantilla_de_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM plantilla_de_actividad WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'plantilla_de_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'plantilla_de_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = public.clasificacion_de_actividad.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesglose($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = public.desglose.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = public.clasificacion_de_actividad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesgloseBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = public.desglose.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["plantilla_de_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["clasificacion_de_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.clasificacion_de_actividad ON plantilla_de_actividad.clasificacion = clasificacion_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "plantilla_de_actividad" and "public.desglose, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithDesgloseByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["plantilla_de_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["desglose"]); 
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
               $r = $this->getAnything("SELECT $fields FROM plantilla_de_actividad INNER JOIN public.desglose ON plantilla_de_actividad.desglose = desglose.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>