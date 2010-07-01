<?php 
   class LugarFijoModel extends PartModel { 
 
          public function LugarFijoModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: lugar_fijo 
           * @desc: Work with table "lugar_fijo" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "lugar_fijo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM lugar_fijo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "lugar_fijo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($grupo,$lugar){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO lugar_fijo (grupo,lugar) VALUES (".(grupo==null?'null':"'".$grupo."'").",".(lugar==null?'null':"'".$lugar."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "lugar_fijo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($grupo,$lugar){ 
                  return $this->getAnything("DELETE FROM lugar_fijo WHERE  grupo = '$grupo'  AND  lugar = '$lugar' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'lugar_fijo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM lugar_fijo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'lugar_fijo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM lugar_fijo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar_fijo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE lugar_fijo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'lugar_fijo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE lugar_fijo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "lugar_fijo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($grupo,$lugar){ 
                  $r = $this->getAnything("SELECT * FROM lugar_fijo WHERE  grupo = '$grupo'  AND  lugar = '$lugar' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'lugar_fijo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_fijo;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'lugar_fijo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_fijo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'lugar_fijo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_fijo LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'lugar_fijo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM lugar_fijo WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.grupo ON lugar_fijo.grupo = public.grupo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.lugar ON lugar_fijo.lugar = public.lugar.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.grupo ON lugar_fijo.grupo = public.grupo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.lugar ON lugar_fijo.lugar = public.lugar.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["lugar_fijo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.grupo ON lugar_fijo.grupo = grupo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "lugar_fijo" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["lugar_fijo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM lugar_fijo INNER JOIN public.lugar ON lugar_fijo.lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>