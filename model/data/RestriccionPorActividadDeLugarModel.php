<?php 
   class RestriccionPorActividadDeLugarModel extends PartModel { 
 
          public function RestriccionPorActividadDeLugarModel($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_por_actividad_de_lugar 
           * @desc: Work with table "restriccion_por_actividad_de_lugar" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_por_actividad_de_lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_por_actividad_de_lugar; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "restriccion_por_actividad_de_lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($restriccion_nominal,$actividad){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO restriccion_por_actividad_de_lugar (restriccion_nominal,actividad) VALUES (".(restriccion_nominal==null?'null':"'".$restriccion_nominal."'").",".(actividad==null?'null':"'".$actividad."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_por_actividad_de_lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($restriccion_nominal){ 
                  return $this->getAnything("DELETE FROM restriccion_por_actividad_de_lugar WHERE  restriccion_nominal = '$restriccion_nominal' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_por_actividad_de_lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM restriccion_por_actividad_de_lugar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_por_actividad_de_lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_por_actividad_de_lugar", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_por_actividad_de_lugar SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_por_actividad_de_lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_por_actividad_de_lugar SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_por_actividad_de_lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($restriccion_nominal){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_por_actividad_de_lugar WHERE  restriccion_nominal = '$restriccion_nominal' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_por_actividad_de_lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_por_actividad_de_lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_por_actividad_de_lugar' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_por_actividad_de_lugar' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.actividad ON restriccion_por_actividad_de_lugar.actividad = public.actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.restriccion_de_lugar" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.restriccion_de_lugar ON restriccion_por_actividad_de_lugar.restriccion_nominal = public.restriccion_de_lugar.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.actividad ON restriccion_por_actividad_de_lugar.actividad = public.actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.restriccion_de_lugar by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.restriccion_de_lugar ON restriccion_por_actividad_de_lugar.restriccion_nominal = public.restriccion_de_lugar.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_por_actividad_de_lugar"]); 
                    $fieldsforaneas = explode($this->fieldnames["actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.actividad ON restriccion_por_actividad_de_lugar.actividad = actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_por_actividad_de_lugar" and "public.restriccion_de_lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithRestriccionDeLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_por_actividad_de_lugar"]); 
                    $fieldsforaneas = explode($this->fieldnames["restriccion_de_lugar"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_por_actividad_de_lugar INNER JOIN public.restriccion_de_lugar ON restriccion_por_actividad_de_lugar.restriccion_nominal = restriccion_de_lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>