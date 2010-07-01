<?php 
   class ZprioriModel extends PartModel { 
 
          public function ZprioriModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: zpriori 
           * @desc: Work with table "zpriori" 
           * @updated: 20/06/10 10:40:56 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM zpriori; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function add($clasificacion,$periodo,$dia,$turno,$valor){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO zpriori (clasificacion,periodo,dia,turno,valor) VALUES (".(clasificacion==null?'null':"'".$clasificacion."'").",".(periodo==null?'null':"'".$periodo."'").",".(dia==null?'null':$dia).",".(turno==null?'null':$turno).",".(valor==null?'null':$valor).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function remove($clasificacion,$periodo,$dia,$turno){ 
                  return $this->getAnything("DELETE FROM zpriori WHERE  clasificacion = '$clasificacion'  AND  periodo = '$periodo'  AND  dia = $dia  AND  turno = $turno ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM zpriori WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM zpriori", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE zpriori SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE zpriori SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "zpriori" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function get($clasificacion,$periodo,$dia,$turno){ 
                  $r = $this->getAnything("SELECT * FROM zpriori WHERE  clasificacion = '$clasificacion'  AND  periodo = '$periodo'  AND  dia = $dia  AND  turno = $turno ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM zpriori;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM zpriori WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'zpriori' 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM zpriori LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'zpriori' by ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM zpriori WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = public.clasificacion_de_actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo" 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = public.clasificacion_de_actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["zpriori"]); 
                    $fieldsforaneas = explode($this->fieldnames["clasificacion_de_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.clasificacion_de_actividad ON zpriori.clasificacion = clasificacion_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "zpriori" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["zpriori"]); 
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
               $r = $this->getAnything("SELECT $fields FROM zpriori INNER JOIN public.periodo ON zpriori.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>