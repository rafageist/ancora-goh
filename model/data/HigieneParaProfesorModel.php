<?php 
   class HigieneParaProfesorModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: higiene_para_profesor 
           * @desc: Work with table "higiene_para_profesor" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM higiene_para_profesor; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($periodo,$profesor,$gasto_maximo_diario){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO higiene_para_profesor (periodo,profesor,gasto_maximo_diario) VALUES ($1,$2,$3);", array(PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($profesor, "text"), PartModel::sqlValue($gasto_maximo_diario, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($periodo,$profesor,$gasto_maximo_diario){ 
                  return $this->getAnythingParams("DELETE FROM higiene_para_profesor WHERE periodo = $1 AND profesor = $2 AND gasto_maximo_diario = $3;", array(PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($profesor, "text"), PartModel::sqlValue($gasto_maximo_diario, "number")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM higiene_para_profesor WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM higiene_para_profesor WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM higiene_para_profesor", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE higiene_para_profesor SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE higiene_para_profesor SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE higiene_para_profesor SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE higiene_para_profesor SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($periodo,$profesor,$gasto_maximo_diario){ 
                  $r = $this->getAnythingParams("SELECT * FROM higiene_para_profesor WHERE periodo = $1 AND profesor = $2 AND gasto_maximo_diario = $3;", array(PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($profesor, "text"), PartModel::sqlValue($gasto_maximo_diario, "number")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = public.periodo.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = public.profesor.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "higiene_para_profesor.periodo,higiene_para_profesor.profesor,higiene_para_profesor.gasto_maximo_diario,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "higiene_para_profesor.periodo,higiene_para_profesor.profesor,higiene_para_profesor.gasto_maximo_diario,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>