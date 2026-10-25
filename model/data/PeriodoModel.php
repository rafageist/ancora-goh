<?php 
   class PeriodoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: periodo 
           * @desc: Work with table "periodo" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM periodo; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno,$planificacion){ 
             $replicas = array();
             $id = getReplica("periodo");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO periodo (id,descripcion,cant_dias,cant_turnos,hora_inicial,duracion_del_turno,planificacion) VALUES ('$id',".(descripcion==null?'null':"'".$descripcion."'").",".(cant_dias==null?'null':$cant_dias).",".(cant_turnos==null?'null':$cant_turnos).",".(hora_inicial==null?'null':$hora_inicial).",".(duracion_del_turno==null?'null':$duracion_del_turno).",".(planificacion==null?'null':"'".$planificacion."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM periodo WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM periodo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM periodo", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE periodo SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE periodo SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM periodo WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodo;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodo WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'periodo' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodo LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'periodo' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM periodo WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodo" and "public.planificacion" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacion($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM periodo INNER JOIN public.planificacion ON periodo.planificacion = public.planificacion.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodo" and "public.planificacion by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM periodo INNER JOIN public.planificacion ON periodo.planificacion = public.planificacion.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "periodo" and "public.planificacion, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["periodo"]); 
                    $fieldsforaneas = explode($this->fieldnames["planificacion"]); 
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
               $r = $this->getAnything("SELECT $fields FROM periodo INNER JOIN public.planificacion ON periodo.planificacion = planificacion.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>