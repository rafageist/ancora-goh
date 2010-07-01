<?php 
   class BrigadaDeGrupoModel extends PartModel { 
 
          public function BrigadaDeGrupoModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: brigada_de_grupo 
           * @desc: Work with table "brigada_de_grupo" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM brigada_de_grupo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($brigada,$grupo){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO brigada_de_grupo (brigada,grupo) VALUES (".(brigada==null?'null':"'".$brigada."'").",".(grupo==null?'null':"'".$grupo."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($brigada,$grupo){ 
                  return $this->getAnything("DELETE FROM brigada_de_grupo WHERE  brigada = '$brigada'  AND  grupo = '$grupo' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM brigada_de_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM brigada_de_grupo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE brigada_de_grupo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE brigada_de_grupo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "brigada_de_grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($brigada,$grupo){ 
                  $r = $this->getAnything("SELECT * FROM brigada_de_grupo WHERE  brigada = '$brigada'  AND  grupo = '$grupo' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada_de_grupo;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada_de_grupo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'brigada_de_grupo' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada_de_grupo LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'brigada_de_grupo' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada_de_grupo WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = public.brigada.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = public.grupo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = public.brigada.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = public.grupo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["brigada_de_grupo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.brigada ON brigada_de_grupo.brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada_de_grupo" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["brigada_de_grupo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM brigada_de_grupo INNER JOIN public.grupo ON brigada_de_grupo.grupo = grupo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>