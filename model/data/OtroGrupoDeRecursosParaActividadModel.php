<?php 
   class OtroGrupoDeRecursosParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: otro_grupo_de_recursos_para_actividad 
           * @desc: Work with table "otro_grupo_de_recursos_para_actividad" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM otro_grupo_de_recursos_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->imprescindibles)) {if ($obj->imprescindibles == "f") $obj->imprescindibles = false; else $obj->imprescindibles = true; }
             } else {
                  if (isset($obj["imprescindibles"])) {if ($obj["imprescindibles"] == "f") $obj["imprescindibles"] = false; else $obj["imprescindibles"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($plantilla,$prioridad,$necesarios,$imprescindibles,$creador){ 
             $replicas = array();
             $id = getReplica("otro_grupo_de_recursos_para_actividad");
             $replicas["id"] = $id;
               if ($imprescindibles == true || $imprescindibles == 1 || $imprescindibles == "t") $imprescindibles = "true"; 
               else if ($imprescindibles== true || $imprescindibles == 0 || $imprescindibles == "f") $imprescindibles = "false"; 
             $r = $this->getAnything("INSERT INTO otro_grupo_de_recursos_para_actividad (id,plantilla,prioridad,necesarios,imprescindibles,creador) VALUES ('$id',".("'".$plantilla."'").",".($prioridad).",".($necesarios).",".($imprescindibles).",".("'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM otro_grupo_de_recursos_para_actividad WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM otro_grupo_de_recursos_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM otro_grupo_de_recursos_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE otro_grupo_de_recursos_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE otro_grupo_de_recursos_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM otro_grupo_de_recursos_para_actividad WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad WHERE $where;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otro_grupo_de_recursos_para_actividad' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otro_grupo_de_recursos_para_actividad' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = seguridad.usuario.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = public.plantilla_de_actividad.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = seguridad.usuario.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = public.plantilla_de_actividad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otro_grupo_de_recursos_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN seguridad.usuario ON otro_grupo_de_recursos_para_actividad.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_grupo_de_recursos_para_actividad" and "public.plantilla_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otro_grupo_de_recursos_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otro_grupo_de_recursos_para_actividad INNER JOIN public.plantilla_de_actividad ON otro_grupo_de_recursos_para_actividad.plantilla = plantilla_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>