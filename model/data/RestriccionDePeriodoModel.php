<?php 
   class RestriccionDePeriodoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_de_periodo 
           * @desc: Work with table "restriccion_de_periodo" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_de_periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_de_periodo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_de_periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($periodo,$dia,$turno,$valor){ 
             $replicas = array();
             $id = getReplica("restriccion_de_periodo");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO restriccion_de_periodo (id,periodo,dia,turno,valor) VALUES ('$id',".(periodo==null?'null':"'".$periodo."'").",".(dia==null?'null':$dia).",".(turno==null?'null':$turno).",".(valor==null?'null':$valor).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_de_periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM restriccion_de_periodo WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_de_periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM restriccion_de_periodo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_de_periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_de_periodo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_de_periodo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_de_periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_de_periodo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_de_periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_de_periodo WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_de_periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_de_periodo;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_de_periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_de_periodo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_de_periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_de_periodo LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_de_periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_de_periodo WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_periodo" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_periodo INNER JOIN public.periodo ON restriccion_de_periodo.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_periodo" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_periodo INNER JOIN public.periodo ON restriccion_de_periodo.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_de_periodo" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_de_periodo"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_de_periodo INNER JOIN public.periodo ON restriccion_de_periodo.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>