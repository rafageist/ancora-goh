<?php 
   class ActividadModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: actividad 
           * @desc: Work with table "actividad" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM actividad; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador){ 
             $replicas = array();
             $id = getReplica("actividad");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO actividad (id,plantilla,grupo,profesor,lugar,dia,turno,momento_de_colocacion,creador) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($plantilla, "text"), PartModel::sqlValue($grupo, "text"), PartModel::sqlValue($profesor, "text"), PartModel::sqlValue($lugar, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($momento_de_colocacion, "number"), PartModel::sqlValue($creador, "text")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM actividad WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM actividad WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM actividad", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE actividad SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE actividad SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE actividad SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE actividad SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM actividad WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'actividad' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'actividad' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN seguridad.usuario ON actividad.creador = seguridad.usuario.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.grupo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.grupo ON actividad.grupo = public.grupo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.lugar ON actividad.lugar = public.lugar.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.plantilla_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.plantilla_de_actividad ON actividad.plantilla = public.plantilla_de_actividad.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.profesor ON actividad.profesor = public.profesor.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN seguridad.usuario ON actividad.creador = seguridad.usuario.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.grupo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.grupo ON actividad.grupo = public.grupo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.lugar ON actividad.lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.plantilla_de_actividad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.plantilla_de_actividad ON actividad.plantilla = public.plantilla_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.profesor ON actividad.profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "actividad.id,actividad.plantilla,actividad.grupo,actividad.profesor,actividad.lugar,actividad.dia,actividad.turno,actividad.momento_de_colocacion,actividad.creador,seguridad.usuario.id,seguridad.usuario.nombre,seguridad.usuario.info,seguridad.usuario.correo,seguridad.usuario.momento_ingreso,seguridad.usuario.nick,seguridad.usuario.contrasena";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN seguridad.usuario ON actividad.creador = seguridad.usuario.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.grupo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithGrupoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "actividad.id,actividad.plantilla,actividad.grupo,actividad.profesor,actividad.lugar,actividad.dia,actividad.turno,actividad.momento_de_colocacion,actividad.creador,public.grupo.id,public.grupo.clasificacion,public.grupo.descripcion,public.grupo.especialidad,public.grupo.nivel,public.grupo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.grupo ON actividad.grupo = public.grupo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "actividad.id,actividad.plantilla,actividad.grupo,actividad.profesor,actividad.lugar,actividad.dia,actividad.turno,actividad.momento_de_colocacion,actividad.creador,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.lugar ON actividad.lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.plantilla_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlantillaDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "actividad.id,actividad.plantilla,actividad.grupo,actividad.profesor,actividad.lugar,actividad.dia,actividad.turno,actividad.momento_de_colocacion,actividad.creador,public.plantilla_de_actividad.id,public.plantilla_de_actividad.desglose,public.plantilla_de_actividad.orden,public.plantilla_de_actividad.clasificacion,public.plantilla_de_actividad.exige_profesor,public.plantilla_de_actividad.exige_lugar";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.plantilla_de_actividad ON actividad.plantilla = public.plantilla_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "actividad" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "actividad.id,actividad.plantilla,actividad.grupo,actividad.profesor,actividad.lugar,actividad.dia,actividad.turno,actividad.momento_de_colocacion,actividad.creador,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM actividad INNER JOIN public.profesor ON actividad.profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>