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
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
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
             $r = $this->getAnythingParams("INSERT INTO otros_recursos_usados (otro_recurso,actividad,creador) VALUES ($1,$2,$3);", array(PartModel::sqlValue($otro_recurso, "text"), PartModel::sqlValue($actividad, "text"), PartModel::sqlValue($creador, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($otro_recurso,$actividad){ 
                  return $this->getAnythingParams("DELETE FROM otros_recursos_usados WHERE otro_recurso = $1 AND actividad = $2;", array(PartModel::sqlValue($otro_recurso, "text"), PartModel::sqlValue($actividad, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM otros_recursos_usados WHERE $where;", $params, $asobjects);
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
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otros_recursos_usados SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE otros_recursos_usados SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE otros_recursos_usados SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE otros_recursos_usados SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "otros_recursos_usados" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($otro_recurso,$actividad){ 
                  $r = $this->getAnythingParams("SELECT * FROM otros_recursos_usados WHERE otro_recurso = $1 AND actividad = $2;", array(PartModel::sqlValue($otro_recurso, "text"), PartModel::sqlValue($actividad, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'otros_recursos_usados' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'otros_recursos_usados' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = public.actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = seguridad.usuario.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = public.otro_recurso.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = public.actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = seguridad.usuario.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otros_recursos_usados.otro_recurso,otros_recursos_usados.actividad,otros_recursos_usados.creador,public.actividad.id,public.actividad.plantilla,public.actividad.grupo,public.actividad.profesor,public.actividad.lugar,public.actividad.dia,public.actividad.turno,public.actividad.momento_de_colocacion,public.actividad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.actividad ON otros_recursos_usados.actividad = public.actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otros_recursos_usados.otro_recurso,otros_recursos_usados.actividad,otros_recursos_usados.creador,seguridad.usuario.id,seguridad.usuario.nombre,seguridad.usuario.info,seguridad.usuario.correo,seguridad.usuario.momento_ingreso,seguridad.usuario.nick,seguridad.usuario.contrasena";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN seguridad.usuario ON otros_recursos_usados.creador = seguridad.usuario.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "otros_recursos_usados" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "otros_recursos_usados.otro_recurso,otros_recursos_usados.actividad,otros_recursos_usados.creador,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM otros_recursos_usados INNER JOIN public.otro_recurso ON otros_recursos_usados.otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>