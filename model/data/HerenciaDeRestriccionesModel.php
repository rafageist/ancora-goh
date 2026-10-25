<?php 
   class HerenciaDeRestriccionesModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: herencia_de_restricciones 
           * @desc: Work with table "herencia_de_restricciones" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM herencia_de_restricciones; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->activa)) {if ($obj->activa == "f") $obj->activa = false; else $obj->activa = true; }
             } else {
                  if (isset($obj["activa"])) {if ($obj["activa"] == "f") $obj["activa"] = false; else $obj["activa"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso){ 
             $replicas = array();
             $id = getReplica("herencia_de_restricciones");
             $replicas["id"] = $id;
               if ($activa == true || $activa == 1 || $activa == "t") $activa = "true"; 
               else if ($activa== true || $activa == 0 || $activa == "f") $activa = "false"; 
             $r = $this->getAnything("INSERT INTO herencia_de_restricciones (id,planificacion,activa,hijo_brigada,hijo_profesor,hijo_lugar,hijo_otro_recurso,padre_brigada,padre_profesor,padre_lugar,padre_otro_recurso) VALUES ('$id',".("'".$planificacion."'").",".($activa).",".("'".$hijo_brigada."'").",".("'".$hijo_profesor."'").",".("'".$hijo_lugar."'").",".("'".$hijo_otro_recurso."'").",".("'".$padre_brigada."'").",".("'".$padre_profesor."'").",".("'".$padre_lugar."'").",".("'".$padre_otro_recurso."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM herencia_de_restricciones WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM herencia_de_restricciones WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM herencia_de_restricciones", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE herencia_de_restricciones SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE herencia_de_restricciones SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM herencia_de_restricciones WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'herencia_de_restricciones' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'herencia_de_restricciones' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = public.brigada.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = public.lugar.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = public.otro_recurso.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = public.profesor.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = public.brigada.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = public.lugar.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = public.otro_recurso.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = public.profesor.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacion($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = public.planificacion.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = public.brigada.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = public.lugar.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = public.otro_recurso.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = public.profesor.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = public.brigada.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = public.lugar.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = public.otro_recurso.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = public.profesor.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = public.planificacion.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
                    $fieldsforaneas = explode($this->fieldnames["brigada"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.hijo_brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
                    $fieldsforaneas = explode($this->fieldnames["lugar"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.hijo_lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.hijo_otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.hijo_profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
                    $fieldsforaneas = explode($this->fieldnames["brigada"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.brigada ON herencia_de_restricciones.padre_brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
                    $fieldsforaneas = explode($this->fieldnames["lugar"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.lugar ON herencia_de_restricciones.padre_lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.otro_recurso ON herencia_de_restricciones.padre_otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.profesor ON herencia_de_restricciones.padre_profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "herencia_de_restricciones" and "public.planificacion, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["herencia_de_restricciones"]); 
                    $fieldsforaneas = explode($this->fieldnames["planificacion"]); 
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
               $r = $this->getAnything("SELECT $fields FROM herencia_de_restricciones INNER JOIN public.planificacion ON herencia_de_restricciones.planificacion = planificacion.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>