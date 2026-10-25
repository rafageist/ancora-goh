<?php 
   class DesgloseModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: desglose 
           * @desc: Work with table "desglose" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM desglose; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->respetar_orden)) {if ($obj->respetar_orden == "f") $obj->respetar_orden = false; else $obj->respetar_orden = true; }
                  if (isset($obj->mismo_dia)) {if ($obj->mismo_dia == "f") $obj->mismo_dia = false; else $obj->mismo_dia = true; }
             } else {
                  if (isset($obj["respetar_orden"])) {if ($obj["respetar_orden"] == "f") $obj["respetar_orden"] = false; else $obj["respetar_orden"] = true; }
                  if (isset($obj["mismo_dia"])) {if ($obj["mismo_dia"] == "f") $obj["mismo_dia"] = false; else $obj["mismo_dia"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($asignatura,$periodo,$min_dias,$max_dias,$respetar_orden,$mismo_dia){ 
             $replicas = array();
             $id = getReplica("desglose");
             $replicas["id"] = $id;
               if ($respetar_orden == true || $respetar_orden == 1 || $respetar_orden == "t") $respetar_orden = "true"; 
               else if ($respetar_orden== true || $respetar_orden == 0 || $respetar_orden == "f") $respetar_orden = "false"; 
               if ($mismo_dia == true || $mismo_dia == 1 || $mismo_dia == "t") $mismo_dia = "true"; 
               else if ($mismo_dia== true || $mismo_dia == 0 || $mismo_dia == "f") $mismo_dia = "false"; 
             $r = $this->getAnything("INSERT INTO desglose (id,asignatura,periodo,min_dias,max_dias,respetar_orden,mismo_dia) VALUES ('$id',".(asignatura==null?'null':"'".$asignatura."'").",".(periodo==null?'null':"'".$periodo."'").",".(min_dias==null?'null':$min_dias).",".(max_dias==null?'null':$max_dias).",".(respetar_orden==null?'null':$respetar_orden).",".(mismo_dia==null?'null':$mismo_dia).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM desglose WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM desglose WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM desglose", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE desglose SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE desglose SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "desglose" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM desglose WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'desglose' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'desglose' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignatura($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = public.asignatura.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.periodo ON desglose.periodo = public.periodo.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = public.asignatura.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.periodo ON desglose.periodo = public.periodo.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.asignatura, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["desglose"]); 
                    $fieldsforaneas = explode($this->fieldnames["asignatura"]); 
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
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.asignatura ON desglose.asignatura = asignatura.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "desglose" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["desglose"]); 
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
               $r = $this->getAnything("SELECT $fields FROM desglose INNER JOIN public.periodo ON desglose.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>