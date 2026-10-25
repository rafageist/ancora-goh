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
             $r = $this->getAnything("INSERT INTO higiene_para_profesor (periodo,profesor,gasto_maximo_diario) VALUES (".(periodo==null?'null':"'".$periodo."'").",".(profesor==null?'null':"'".$profesor."'").",".(gasto_maximo_diario==null?'null':$gasto_maximo_diario).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($periodo,$profesor,$gasto_maximo_diario){ 
                  return $this->getAnything("DELETE FROM higiene_para_profesor WHERE  periodo = '$periodo'  AND  profesor = '$profesor'  AND  gasto_maximo_diario = $gasto_maximo_diario ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
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
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE higiene_para_profesor SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE higiene_para_profesor SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "higiene_para_profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($periodo,$profesor,$gasto_maximo_diario){ 
                  $r = $this->getAnything("SELECT * FROM higiene_para_profesor WHERE  periodo = '$periodo'  AND  profesor = '$profesor'  AND  gasto_maximo_diario = $gasto_maximo_diario ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_profesor;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_profesor WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'higiene_para_profesor' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_profesor LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'higiene_para_profesor' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_profesor WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = public.profesor.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = public.profesor.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["higiene_para_profesor"]); 
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
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.periodo ON higiene_para_profesor.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_profesor" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["higiene_para_profesor"]); 
                    $fieldsforaneas = explode($this->fieldnames["profesor"]); 
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
               $r = $this->getAnything("SELECT $fields FROM higiene_para_profesor INNER JOIN public.profesor ON higiene_para_profesor.profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>