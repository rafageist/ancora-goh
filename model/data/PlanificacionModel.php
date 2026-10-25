<?php 
   class PlanificacionModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: planificacion 
           * @desc: Work with table "planificacion" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM planificacion; "); 
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
            * @desc: Add "planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($descripcion,$fecha_inicial,$cant_dias,$activa,$creador){ 
             $replicas = array();
             $id = getReplica("planificacion");
             $replicas["id"] = $id;
               if ($activa == true || $activa == 1 || $activa == "t") $activa = "true"; 
               else if ($activa== true || $activa == 0 || $activa == "f") $activa = "false"; 
             $r = $this->getAnything("INSERT INTO planificacion (id,descripcion,fecha_inicial,cant_dias,activa,creador) VALUES ('$id',".("'".$descripcion."'").",".($fecha_inicial).",".($cant_dias).",".($activa).",".("'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM planificacion WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'planificacion' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM planificacion WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'planificacion' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM planificacion", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'planificacion' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE planificacion SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'planificacion' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE planificacion SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM planificacion WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'planificacion' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'planificacion' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'planificacion' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'planificacion' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "planificacion" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion INNER JOIN seguridad.usuario ON planificacion.creador = seguridad.usuario.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "planificacion" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM planificacion INNER JOIN seguridad.usuario ON planificacion.creador = seguridad.usuario.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "planificacion" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["planificacion"]); 
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
               $r = $this->getAnything("SELECT $fields FROM planificacion INNER JOIN seguridad.usuario ON planificacion.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>