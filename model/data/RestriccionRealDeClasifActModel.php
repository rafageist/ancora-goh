<?php 
   class RestriccionRealDeClasifActModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_real_de_clasif_act 
           * @desc: Work with table "restriccion_real_de_clasif_act" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_real_de_clasif_act; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->por_actividad)) {if ($obj->por_actividad == "f") $obj->por_actividad = false; else $obj->por_actividad = true; }
             } else {
                  if (isset($obj["por_actividad"])) {if ($obj["por_actividad"] == "f") $obj["por_actividad"] = false; else $obj["por_actividad"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$periodicidad,$fecha,$hora_inicial,$hora_final,$por_actividad,$actividad,$objeto){ 
             $replicas = array();
               if ($por_actividad == true || $por_actividad == 1 || $por_actividad == "t") $por_actividad = "true"; 
               else if ($por_actividad== true || $por_actividad == 0 || $por_actividad == "f") $por_actividad = "false"; 
             $r = $this->getAnything("INSERT INTO restriccion_real_de_clasif_act (restriccion_nominal,periodicidad,fecha,hora_inicial,hora_final,por_actividad,actividad,objeto) VALUES (".(restriccion_nominal==null?'null':"'".$restriccion_nominal."'").",".(periodicidad==null?'null':"'".$periodicidad."'").",".(fecha==null?'null':$fecha).",".(hora_inicial==null?'null':$hora_inicial).",".(hora_final==null?'null':$hora_final).",".(por_actividad==null?'null':$por_actividad).",".(actividad==null?'null':"'".$actividad."'").",".(objeto==null?'null':"'".$objeto."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal,$periodicidad){ 
                  return $this->getAnything("DELETE FROM restriccion_real_de_clasif_act WHERE  restriccion_nominal = '$restriccion_nominal'  AND  periodicidad = '$periodicidad' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM restriccion_real_de_clasif_act WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_real_de_clasif_act", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_real_de_clasif_act SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_real_de_clasif_act SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_real_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal,$periodicidad){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_real_de_clasif_act WHERE  restriccion_nominal = '$restriccion_nominal'  AND  periodicidad = '$periodicidad' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_real_de_clasif_act' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_real_de_clasif_act' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = public.clasificacion_de_actividad.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = public.periodicidad.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifAct($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = public.restriccion_de_clasif_act.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = public.clasificacion_de_actividad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = public.periodicidad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifActBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = public.restriccion_de_clasif_act.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_real_de_clasif_act"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.clasificacion_de_actividad ON restriccion_real_de_clasif_act.objeto = clasificacion_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.periodicidad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodicidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_real_de_clasif_act"]); 
                    $fieldsforaneas = explode($this->fieldnames["periodicidad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.periodicidad ON restriccion_real_de_clasif_act.periodicidad = periodicidad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_real_de_clasif_act" and "public.restriccion_de_clasif_act, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeClasifActByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_real_de_clasif_act"]); 
                    $fieldsforaneas = explode($this->fieldnames["restriccion_de_clasif_act"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_real_de_clasif_act INNER JOIN public.restriccion_de_clasif_act ON restriccion_real_de_clasif_act.restriccion_nominal = restriccion_de_clasif_act.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>