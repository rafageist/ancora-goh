<?php 
   class OtroRecursoParaActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: otro_recurso_para_actividad 
           * @desc: Work with table "otro_recurso_para_actividad" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM otro_recurso_para_actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($grupo_de_recursos,$prioridad,$otro_recurso){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO otro_recurso_para_actividad (grupo_de_recursos,prioridad,otro_recurso) VALUES (".(grupo_de_recursos==null?'null':"'".$grupo_de_recursos."'").",".(prioridad==null?'null':$prioridad).",".(otro_recurso==null?'null':"'".$otro_recurso."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($grupo_de_recursos,$otro_recurso){ 
                  return $this->getAnything("DELETE FROM otro_recurso_para_actividad WHERE  grupo_de_recursos = '$grupo_de_recursos'  AND  otro_recurso = '$otro_recurso' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM otro_recurso_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM otro_recurso_para_actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE otro_recurso_para_actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE otro_recurso_para_actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otro_recurso_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($grupo_de_recursos,$otro_recurso){ 
                  $r = $this->getAnything("SELECT * FROM otro_recurso_para_actividad WHERE  grupo_de_recursos = '$grupo_de_recursos'  AND  otro_recurso = '$otro_recurso' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otro_recurso_para_actividad' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otro_recurso_para_actividad' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = public.otro_grupo_de_recursos_para_actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = public.otro_recurso.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = public.otro_grupo_de_recursos_para_actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = public.otro_recurso.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_grupo_de_recursos_para_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroGrupoDeRecursosParaActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otro_recurso_para_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["otro_grupo_de_recursos_para_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_grupo_de_recursos_para_actividad ON otro_recurso_para_actividad.grupo_de_recursos = otro_grupo_de_recursos_para_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otro_recurso_para_actividad" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otro_recurso_para_actividad"]); 
                    $fieldsforaneas = explode($this->fieldnames["otro_recurso"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otro_recurso_para_actividad INNER JOIN public.otro_recurso ON otro_recurso_para_actividad.otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>