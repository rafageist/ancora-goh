<?php 
   class ExcepcionDeHrtModel extends PartModel { 
 
          public function ExcepcionDeHrtModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: excepcion_de_hrt 
           * @desc: Work with table "excepcion_de_hrt" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM excepcion_de_hrt; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($herencia,$periodo,$dia,$turno){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO excepcion_de_hrt (herencia,periodo,dia,turno) VALUES (".(herencia==null?'null':"'".$herencia."'").",".(periodo==null?'null':"'".$periodo."'").",".(dia==null?'null':$dia).",".(turno==null?'null':$turno).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($herencia,$periodo,$dia,$turno){ 
                  return $this->getAnything("DELETE FROM excepcion_de_hrt WHERE  herencia = '$herencia'  AND  periodo = '$periodo'  AND  dia = $dia  AND  turno = $turno ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM excepcion_de_hrt WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM excepcion_de_hrt", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE excepcion_de_hrt SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE excepcion_de_hrt SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "excepcion_de_hrt" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($herencia,$periodo,$dia,$turno){ 
                  $r = $this->getAnything("SELECT * FROM excepcion_de_hrt WHERE  herencia = '$herencia'  AND  periodo = '$periodo'  AND  dia = $dia  AND  turno = $turno ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM excepcion_de_hrt;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM excepcion_de_hrt WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'excepcion_de_hrt' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM excepcion_de_hrt LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'excepcion_de_hrt' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM excepcion_de_hrt WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestricciones($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = public.herencia_de_restricciones.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestriccionesBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = public.herencia_de_restricciones.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.herencia_de_restricciones, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithHerenciaDeRestriccionesByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["excepcion_de_hrt"]); 
                    $fieldsforaneas = explode($this->fieldnames["herencia_de_restricciones"]); 
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
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.herencia_de_restricciones ON excepcion_de_hrt.herencia = herencia_de_restricciones.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "excepcion_de_hrt" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["excepcion_de_hrt"]); 
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
               $r = $this->getAnything("SELECT $fields FROM excepcion_de_hrt INNER JOIN public.periodo ON excepcion_de_hrt.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>