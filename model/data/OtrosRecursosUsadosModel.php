<?php 
   class OtrosRecursosUsadosModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: otros_recursos_usados 
           * @desc: Work with table "otros_recursos_usados" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM otros_recursos_usados; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($otro_recurso,$actividad,$creador){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO otros_recursos_usados (otro_recurso,actividad,creador) VALUES (".("'".$otro_recurso."'").",".("'".$actividad."'").",".("'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($otro_recurso,$actividad){ 
                  return $this->getAnything("DELETE FROM otros_recursos_usados WHERE  otro_recurso = '$otro_recurso'  AND  actividad = '$actividad' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM otros_recursos_usados WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM otros_recursos_usados", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE otros_recursos_usados SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE otros_recursos_usados SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($otro_recurso,$actividad){ 
                  $r = $this->getAnything("SELECT * FROM otros_recursos_usados WHERE  otro_recurso = '$otro_recurso'  AND  actividad = '$actividad' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otros_recursos_usados;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otros_recursos_usados WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otros_recursos_usados LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM otros_recursos_usados WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = public.actividad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = public.otro_recurso.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = public.actividad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = public.otro_recurso.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otros_recursos_usados"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otros_recursos_usados"]); 
                    $fieldsforaneas = explode($this->fieldnames["usuario"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["otros_recursos_usados"]); 
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
               $r = $this->getAnything("SELECT $fields FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>