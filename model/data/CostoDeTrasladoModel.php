<?php 
   class CostoDeTrasladoModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: costo_de_traslado 
           * @desc: Work with table "costo_de_traslado" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "costo_de_traslado" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM costo_de_traslado; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "costo_de_traslado" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($origen,$destino,$costo){ 
             $replicas = array();
             $r = $this->getAnythingParams("INSERT INTO costo_de_traslado (origen,destino,costo) VALUES ($1,$2,$3);", array(PartModel::sqlValue($origen, "text"), PartModel::sqlValue($destino, "text"), PartModel::sqlValue($costo, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "costo_de_traslado" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($origen,$destino){ 
                  return $this->getAnythingParams("DELETE FROM costo_de_traslado WHERE origen = $1 AND destino = $2;", array(PartModel::sqlValue($origen, "text"), PartModel::sqlValue($destino, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'costo_de_traslado' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM costo_de_traslado WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM costo_de_traslado WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'costo_de_traslado' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM costo_de_traslado", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'costo_de_traslado' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE costo_de_traslado SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE costo_de_traslado SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'costo_de_traslado' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE costo_de_traslado SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE costo_de_traslado SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "costo_de_traslado" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($origen,$destino){ 
                  $r = $this->getAnythingParams("SELECT * FROM costo_de_traslado WHERE origen = $1 AND destino = $2;", array(PartModel::sqlValue($origen, "text"), PartModel::sqlValue($destino, "text")), true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'costo_de_traslado' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado;",$asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'costo_de_traslado' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'costo_de_traslado' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'costo_de_traslado' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.destino = public.lugar.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.origen = public.lugar.id;", $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.destino = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.origen = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "costo_de_traslado.origen,costo_de_traslado.destino,costo_de_traslado.costo,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.destino = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "costo_de_traslado" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "costo_de_traslado.origen,costo_de_traslado.destino,costo_de_traslado.costo,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM costo_de_traslado INNER JOIN public.lugar ON costo_de_traslado.origen = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
             return $r; 
          } 
    } 
?>