<?php 
   class RestriccionDeOtroRecursoModel extends PartModel { 
 
          public function RestriccionDeOtroRecursoModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_de_otro_recurso 
           * @desc: Work with table "restriccion_de_otro_recurso" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_de_otro_recurso; "); 
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
            * @desc: Add "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($otro_recurso,$periodo,$dia,$turno,$valor,$por_actividad,$actividad){ 
             $replicas = array();
             $id = getReplica("restriccion_de_otro_recurso");
             $replicas["id"] = $id;
               if ($por_actividad == true || $por_actividad == 1 || $por_actividad == "t") $por_actividad = "true"; 
               else if ($por_actividad== true || $por_actividad == 0 || $por_actividad == "f") $por_actividad = "false"; 
             $r = $this->getAnything("INSERT INTO restriccion_de_otro_recurso (id,otro_recurso,periodo,dia,turno,valor,por_actividad,actividad) VALUES ('$id',".(otro_recurso==null?'null':"'".$otro_recurso."'").",".(periodo==null?'null':"'".$periodo."'").",".(dia==null?'null':$dia).",".(turno==null?'null':$turno).",".(valor==null?'null':$valor).",".(por_actividad==null?'null':$por_actividad).",".(actividad==null?'null':"'".$actividad."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM restriccion_de_otro_recurso WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM restriccion_de_otro_recurso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_de_otro_recurso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_de_otro_recurso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_de_otro_recurso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_de_otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_de_otro_recurso WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_de_otro_recurso' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_de_otro_recurso' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = public.actividad.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = public.otro_recurso.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = public.periodo.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = public.actividad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = public.otro_recurso.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = public.periodo.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_de_otro_recurso"]); 
                    $fieldsforaneas = explode($this->fieldnames["actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.actividad ON restriccion_de_otro_recurso.actividad = actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_de_otro_recurso"]); 
                    $fieldsforaneas = explode($this->fieldnames["otro_recurso"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.otro_recurso ON restriccion_de_otro_recurso.otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_otro_recurso" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_de_otro_recurso"]); 
                    $fieldsforaneas = explode($this->fieldnames["periodo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_otro_recurso INNER JOIN public.periodo ON restriccion_de_otro_recurso.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>