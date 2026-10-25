<?php 
   class AccesoProhibidoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: acceso_prohibido 
           * @desc: Work with table "acceso_prohibido" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM acceso_prohibido; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($grupo,$lugar){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO acceso_prohibido (grupo,lugar) VALUES (".(grupo==null?'null':"'".$grupo."'").",".(lugar==null?'null':"'".$lugar."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($grupo,$lugar){ 
                  return $this->getAnything("DELETE FROM acceso_prohibido WHERE  grupo = '$grupo'  AND  lugar = '$lugar' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM acceso_prohibido WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM acceso_prohibido", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE acceso_prohibido SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE acceso_prohibido SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "acceso_prohibido" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($grupo,$lugar){ 
                  $r = $this->getAnything("SELECT * FROM acceso_prohibido WHERE  grupo = '$grupo'  AND  lugar = '$lugar' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM acceso_prohibido;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM acceso_prohibido WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'acceso_prohibido' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM acceso_prohibido LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'acceso_prohibido' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM acceso_prohibido WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = public.grupo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = public.lugar.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = public.grupo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = public.lugar.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["acceso_prohibido"]); 
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
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.grupo ON acceso_prohibido.grupo = grupo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "acceso_prohibido" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["acceso_prohibido"]); 
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
               $r = $this->getAnything("SELECT $fields FROM acceso_prohibido INNER JOIN public.lugar ON acceso_prohibido.lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>