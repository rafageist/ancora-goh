<?php 
   class HigieneParaBrigadaModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: higiene_para_brigada 
           * @desc: Work with table "higiene_para_brigada" 
           * @updated: 20/06/10 10:40:55 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "higiene_para_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM higiene_para_brigada; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          /** 
            * @name: add 
            * @desc: Add "higiene_para_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function add($periodo,$brigada,$gasto_maximo_diario){ 
             $replicas = array();
             $r = $this->getAnything("INSERT INTO higiene_para_brigada (periodo,brigada,gasto_maximo_diario) VALUES (".("'".$periodo."'").",".("'".$brigada."'").",".($gasto_maximo_diario).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "higiene_para_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function remove($periodo,$brigada,$gasto_maximo_diario){ 
                  return $this->getAnything("DELETE FROM higiene_para_brigada WHERE  periodo = '$periodo'  AND  brigada = '$brigada'  AND  gasto_maximo_diario = $gasto_maximo_diario ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'higiene_para_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
                 return $this->getAnything("DELETE FROM higiene_para_brigada WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'higiene_para_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM higiene_para_brigada", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'higiene_para_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE higiene_para_brigada SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'higiene_para_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE higiene_para_brigada SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "higiene_para_brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function get($periodo,$brigada,$gasto_maximo_diario){ 
                  $r = $this->getAnything("SELECT * FROM higiene_para_brigada WHERE  periodo = '$periodo'  AND  brigada = '$brigada'  AND  gasto_maximo_diario = $gasto_maximo_diario ;",true);
                   if (isset($r[0])) return $r[0]; else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'higiene_para_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_brigada;",$asobjects);
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'higiene_para_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_brigada WHERE $where;",$asobjects);
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'higiene_para_brigada' 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_brigada LIMIT $limit OFFSET $offset;",$asobjects);
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'higiene_para_brigada' by ... 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               return $this->getAnything("SELECT $fields FROM higiene_para_brigada WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.brigada" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.brigada ON higiene_para_brigada.brigada = public.brigada.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.periodo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.periodo ON higiene_para_brigada.periodo = public.periodo.id ");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.brigada ON higiene_para_brigada.brigada = public.brigada.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.periodo ON higiene_para_brigada.periodo = public.periodo.id  WHERE $where");
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["higiene_para_brigada"]); 
                    $fieldsforaneas = explode($this->fieldnames["brigada"]); 
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
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.brigada ON higiene_para_brigada.brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "higiene_para_brigada" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["higiene_para_brigada"]); 
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
               $r = $this->getAnything("SELECT $fields FROM higiene_para_brigada INNER JOIN public.periodo ON higiene_para_brigada.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
             return $r; 
          }
    } 
?>