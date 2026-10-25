<?php 
   class PeriodicidadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: periodicidad 
           * @desc: Work with table "periodicidad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM periodicidad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($periodo,$dia){ 
             $replicas = array();
             $id = getReplica("periodicidad");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO periodicidad (id,periodo,dia) VALUES ('$id',".(periodo==null?'null':"'".$periodo."'").",".(dia==null?'null':$dia).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM periodicidad WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'periodicidad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM periodicidad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'periodicidad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM periodicidad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'periodicidad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE periodicidad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'periodicidad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE periodicidad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "periodicidad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM periodicidad WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'periodicidad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodicidad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'periodicidad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodicidad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'periodicidad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodicidad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'periodicidad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodicidad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodicidad" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM periodicidad INNER JOIN public.periodo ON periodicidad.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodicidad" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM periodicidad INNER JOIN public.periodo ON periodicidad.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodicidad" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["periodicidad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM periodicidad INNER JOIN public.periodo ON periodicidad.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>