<?php 
   class BrigadaModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: brigada 
           * @desc: Work with table "brigada" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM brigada; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($descripcion,$especialidad,$nivel,$matricula,$creador){ 
             $replicas = array();
             $id = getReplica("brigada");
             $replicas["id"] = $id;
             $r = $this->getAnything("INSERT INTO brigada (id,descripcion,especialidad,nivel,matricula,creador) VALUES ('$id',".(descripcion==null?'null':"'".$descripcion."'").",".(especialidad==null?'null':"'".$especialidad."'").",".(nivel==null?'null':"'".$nivel."'").",".(matricula==null?'null':$matricula).",".(creador==null?'null':"'".$creador."'").");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM brigada WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'brigada' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM brigada WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'brigada' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM brigada", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE brigada SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'brigada' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE brigada SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM brigada WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'brigada' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'brigada' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'brigada' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'brigada' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM brigada WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "seguridad.usuario" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuario($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN seguridad.usuario ON brigada.creador = seguridad.usuario.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.especialidad ON brigada.especialidad = public.especialidad.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivel($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.nivel ON brigada.nivel = public.nivel.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "seguridad.usuario by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN seguridad.usuario ON brigada.creador = seguridad.usuario.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.especialidad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.especialidad ON brigada.especialidad = public.especialidad.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.nivel by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.nivel ON brigada.nivel = public.nivel.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "seguridad.usuario, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithSeguridadUsuarioByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["brigada"]); 
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
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN seguridad.usuario ON brigada.creador = usuario.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.especialidad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["brigada"]); 
                    $fieldsforaneas = explode($this->fieldnames["especialidad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.especialidad ON brigada.especialidad = especialidad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "brigada" and "public.nivel, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["brigada"]); 
                    $fieldsforaneas = explode($this->fieldnames["nivel"]); 
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
               $r = $this->getAnything("SELECT $fields FROM brigada INNER JOIN public.nivel ON brigada.nivel = nivel.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>