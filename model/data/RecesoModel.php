<?php 
   class RecesoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: receso 
           * @desc: Work with table "receso" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "receso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM receso; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "receso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($periodo,$despues_del_turno,$duracion){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO receso (periodo,despues_del_turno,duracion) VALUES (".(periodo==null?'null':"'".$periodo."'").",".(despues_del_turno==null?'null':$despues_del_turno).",".(duracion==null?'null':$duracion).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "receso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($periodo,$despues_del_turno){ 
                  return $this->getAnything("DELETE FROM receso WHERE  periodo = '$periodo'  AND  despues_del_turno = $despues_del_turno ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'receso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM receso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'receso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM receso", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'receso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE receso SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'receso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE receso SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "receso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($periodo,$despues_del_turno){ 
                  $r = $this->getAnything("SELECT * FROM receso WHERE  periodo = '$periodo'  AND  despues_del_turno = $despues_del_turno ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'receso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM receso;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'receso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM receso WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'receso' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM receso LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'receso' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM receso WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "receso" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM receso INNER JOIN public.periodo ON receso.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "receso" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM receso INNER JOIN public.periodo ON receso.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "receso" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["receso"]); 
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
               $r = $this->getAnything("SELECT $fields FROM receso INNER JOIN public.periodo ON receso.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>