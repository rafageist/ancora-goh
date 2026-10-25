<?php 
   class LugarParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: lugar_para_actividad 
           * @desc: Work with table "lugar_para_actividad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "lugar_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM lugar_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "lugar_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($plantilla,$lugar,$prioridad){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO lugar_para_actividad (plantilla,lugar,prioridad) VALUES (".("'".$plantilla."'").",".("'".$lugar."'").",".($prioridad).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "lugar_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($plantilla,$lugar){ 
                  return $this->getAnything("DELETE FROM lugar_para_actividad WHERE  plantilla = '$plantilla'  AND  lugar = '$lugar' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'lugar_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM lugar_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'lugar_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM lugar_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE lugar_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE lugar_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "lugar_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($plantilla,$lugar){ 
                  $r = $this->getAnything("SELECT * FROM lugar_para_actividad WHERE  plantilla = '$plantilla'  AND  lugar = '$lugar' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'lugar_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_para_actividad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'lugar_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'lugar_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_para_actividad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'lugar_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_para_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.lugar ON lugar_para_actividad.lugar = public.lugar.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.plantilla_de_actividad ON lugar_para_actividad.plantilla = public.plantilla_de_actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.lugar ON lugar_para_actividad.lugar = public.lugar.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.plantilla_de_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.plantilla_de_actividad ON lugar_para_actividad.plantilla = public.plantilla_de_actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["lugar_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.lugar ON lugar_para_actividad.lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_para_actividad" and "public.plantilla_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["lugar_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM lugar_para_actividad INNER JOIN public.plantilla_de_actividad ON lugar_para_actividad.plantilla = plantilla_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>