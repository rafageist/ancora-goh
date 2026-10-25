<?php 
   class RestriccionCondicionalModel extends PartModel { 
 
          public function __construct($db){ 
                $this->db = $db; 
          } 
         /** 
           * @name: restriccion_condicional 
           * @desc: Work with table "restriccion_condicional" 
           * @updated: 20/06/10 10:40:54 
           * @version: 1.0 
           */ 
          /** 
            * @name: length 
            * @desc: Get count of "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function length($fieldcount="id"){ 
              if ($fieldcount !== "*") PartModel::sqlIdentifier($fieldcount);
              $r = $this->getAnything("SELECT count($fieldcount) as c FROM restriccion_condicional; "); 
              if (isset($r[0])) return $r[0]["c"];
              return 0; 
         }
          public function repairBoolean($obj, $asobject = true){
             if ($asobject == true){
                  if (isset($obj->activa)) {if ($obj->activa == "f") $obj->activa = false; else $obj->activa = true; }
                  if (isset($obj->discreto_exacto)) {if ($obj->discreto_exacto == "f") $obj->discreto_exacto = false; else $obj->discreto_exacto = true; }
                  if (isset($obj->discreto_exacto_planif)) {if ($obj->discreto_exacto_planif == "f") $obj->discreto_exacto_planif = false; else $obj->discreto_exacto_planif = true; }
                  if (isset($obj->discreto)) {if ($obj->discreto == "f") $obj->discreto = false; else $obj->discreto = true; }
                  if (isset($obj->hoy)) {if ($obj->hoy == "f") $obj->hoy = false; else $obj->hoy = true; }
                  if (isset($obj->ahora)) {if ($obj->ahora == "f") $obj->ahora = false; else $obj->ahora = true; }
                  if (isset($obj->consec_discreto)) {if ($obj->consec_discreto == "f") $obj->consec_discreto = false; else $obj->consec_discreto = true; }
                  if (isset($obj->siempre)) {if ($obj->siempre == "f") $obj->siempre = false; else $obj->siempre = true; }
             } else {
                  if (isset($obj["activa"])) {if ($obj["activa"] == "f") $obj["activa"] = false; else $obj["activa"] = true; }
                  if (isset($obj["discreto_exacto"])) {if ($obj["discreto_exacto"] == "f") $obj["discreto_exacto"] = false; else $obj["discreto_exacto"] = true; }
                  if (isset($obj["discreto_exacto_planif"])) {if ($obj["discreto_exacto_planif"] == "f") $obj["discreto_exacto_planif"] = false; else $obj["discreto_exacto_planif"] = true; }
                  if (isset($obj["discreto"])) {if ($obj["discreto"] == "f") $obj["discreto"] = false; else $obj["discreto"] = true; }
                  if (isset($obj["hoy"])) {if ($obj["hoy"] == "f") $obj["hoy"] = false; else $obj["hoy"] = true; }
                  if (isset($obj["ahora"])) {if ($obj["ahora"] == "f") $obj["ahora"] = false; else $obj["ahora"] = true; }
                  if (isset($obj["consec_discreto"])) {if ($obj["consec_discreto"] == "f") $obj["consec_discreto"] = false; else $obj["consec_discreto"] = true; }
                  if (isset($obj["siempre"])) {if ($obj["siempre"] == "f") $obj["siempre"] = false; else $obj["siempre"] = true; }
             }
             return $obj;
          }
          /** 
            * @name: add 
            * @desc: Add "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function add($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor){ 
             $replicas = array();
             $id = getReplica("restriccion_condicional");
             $replicas["id"] = $id;
             $r = $this->getAnythingParams("INSERT INTO restriccion_condicional (id,planificacion,activa,clasif_act,asig,brigada,profesor,lugar,otro_recurso,especialidad,nivel,fecha_ini,fecha_fin,hora_ini,hora_fin,discreto_exacto,dia_ini_planif,dia_fin_planif,hora_ini_planif,hora_fin_planif,discreto_exacto_planif,periodo,dia_ini,dia_fin,turno_ini,turno_fin,discreto,hoy,ahora,dia_relativo_anterior,cant_dias_relativo_anterior,dia_relativo_posterior,cant_dias_relativo_posterior,rango_tiempo_relativo_anterior_ini,rango_tiempo_relativo_anterior_tamano,turno_relativo_anterior,cant_turnos_relativo_anterior,turno_relativo_posterior,cant_turnos_relativo_posterior,rango_tiempo_relativo_posterior_ini,rango_tiempo_relativo_posterior_tamano,entre_dias_antes,entre_dias_despues,entre_turnos_antes,entre_turnos_despues,entre_tiempo_antes,entre_tiempo_despues,cant_exa,cant_min,cant_max,consec_brigada,consec_profesor,consec_lugar,consec_otro_recurso,consec_periodo,consec_dia_ini,consec_dia_fin,consec_turno_ini,consec_turno_fin,consec_discreto,siempre,valor) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29,$30,$31,$32,$33,$34,$35,$36,$37,$38,$39,$40,$41,$42,$43,$44,$45,$46,$47,$48,$49,$50,$51,$52,$53,$54,$55,$56,$57,$58,$59,$60,$61,$62);", array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($planificacion, "text"), PartModel::sqlValue($activa, "bool"), PartModel::sqlValue($clasif_act, "text"), PartModel::sqlValue($asig, "text"), PartModel::sqlValue($brigada, "text"), PartModel::sqlValue($profesor, "text"), PartModel::sqlValue($lugar, "text"), PartModel::sqlValue($otro_recurso, "text"), PartModel::sqlValue($especialidad, "text"), PartModel::sqlValue($nivel, "text"), PartModel::sqlValue($fecha_ini, "number"), PartModel::sqlValue($fecha_fin, "number"), PartModel::sqlValue($hora_ini, "number"), PartModel::sqlValue($hora_fin, "number"), PartModel::sqlValue($discreto_exacto, "bool"), PartModel::sqlValue($dia_ini_planif, "number"), PartModel::sqlValue($dia_fin_planif, "number"), PartModel::sqlValue($hora_ini_planif, "number"), PartModel::sqlValue($hora_fin_planif, "number"), PartModel::sqlValue($discreto_exacto_planif, "bool"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia_ini, "number"), PartModel::sqlValue($dia_fin, "number"), PartModel::sqlValue($turno_ini, "number"), PartModel::sqlValue($turno_fin, "number"), PartModel::sqlValue($discreto, "bool"), PartModel::sqlValue($hoy, "bool"), PartModel::sqlValue($ahora, "bool"), PartModel::sqlValue($dia_relativo_anterior, "number"), PartModel::sqlValue($cant_dias_relativo_anterior, "number"), PartModel::sqlValue($dia_relativo_posterior, "number"), PartModel::sqlValue($cant_dias_relativo_posterior, "number"), PartModel::sqlValue($rango_tiempo_relativo_anterior_ini, "number"), PartModel::sqlValue($rango_tiempo_relativo_anterior_tamano, "number"), PartModel::sqlValue($turno_relativo_anterior, "number"), PartModel::sqlValue($cant_turnos_relativo_anterior, "number"), PartModel::sqlValue($turno_relativo_posterior, "number"), PartModel::sqlValue($cant_turnos_relativo_posterior, "number"), PartModel::sqlValue($rango_tiempo_relativo_posterior_ini, "number"), PartModel::sqlValue($rango_tiempo_relativo_posterior_tamano, "number"), PartModel::sqlValue($entre_dias_antes, "number"), PartModel::sqlValue($entre_dias_despues, "number"), PartModel::sqlValue($entre_turnos_antes, "number"), PartModel::sqlValue($entre_turnos_despues, "number"), PartModel::sqlValue($entre_tiempo_antes, "number"), PartModel::sqlValue($entre_tiempo_despues, "number"), PartModel::sqlValue($cant_exa, "number"), PartModel::sqlValue($cant_min, "number"), PartModel::sqlValue($cant_max, "number"), PartModel::sqlValue($consec_brigada, "text"), PartModel::sqlValue($consec_profesor, "text"), PartModel::sqlValue($consec_lugar, "text"), PartModel::sqlValue($consec_otro_recurso, "text"), PartModel::sqlValue($consec_periodo, "text"), PartModel::sqlValue($consec_dia_ini, "number"), PartModel::sqlValue($consec_dia_fin, "number"), PartModel::sqlValue($consec_turno_ini, "number"), PartModel::sqlValue($consec_turno_fin, "number"), PartModel::sqlValue($consec_discreto, "bool"), PartModel::sqlValue($siempre, "bool"), PartModel::sqlValue($valor, "number")));
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnythingParams("DELETE FROM restriccion_condicional WHERE id = $1;", array(PartModel::sqlValue($id, "text")));
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false, $params = null){
                 if (is_array($params)) return $this->getAnythingParams("DELETE FROM restriccion_condicional WHERE $where;", $params, $asobjects);
                 return $this->getAnything("DELETE FROM restriccion_condicional WHERE $where;",$asobjects);
          } 
          /** 
            * @name: clean 
            * @desc: Clean 'restriccion_condicional' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function clean($asobjects = false){ 
               return $this->getAnything("DELETE FROM restriccion_condicional", $asobjects);          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_condicional' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function set($set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_condicional SET $set;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_condicional SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false, $params = null){
               if (is_array($params)) return $this->getAnythingParams("UPDATE restriccion_condicional SET $set WHERE $where;", $params, $asobjects);
               return $this->getAnything("UPDATE restriccion_condicional SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnythingParams("SELECT * FROM restriccion_condicional WHERE id = $1;", array(PartModel::sqlValue($id, "text")), true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_condicional' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimit 
            * @desc: Limited get 'restriccion_condicional' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimit($limit, $offset, $fields = "*", $asobjects = false){
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional LIMIT $1 OFFSET $2;", array(PartModel::sqlValue($limit, "number"), PartModel::sqlValue($offset, "number")), $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false, $params = null){
               $params = is_array($params) ? array_values($params) : array();
               $n = count($params);
               $params[] = PartModel::sqlValue($limit, "number");
               $params[] = PartModel::sqlValue($offset, "number");
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional WHERE $where LIMIT \$".($n + 1)." OFFSET \$".($n + 2).";", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.asignatura" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignatura($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = public.asignatura.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigada($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = public.brigada.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.clasificacion_de_actividad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = public.clasificacion_de_actividad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = public.brigada.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugar($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = public.lugar.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecurso($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodo($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = public.periodo.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesor($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = public.profesor.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidad($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = public.especialidad.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = public.lugar.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivel($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = public.nivel.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = public.otro_recurso.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = public.periodo.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacion($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = public.planificacion.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorA($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = public.profesor.id;", $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.asignatura by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = public.asignatura.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = public.brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = public.clasificacion_de_actividad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = public.brigada.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.especialidad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = public.especialidad.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = public.lugar.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.nivel by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = public.nivel.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = public.otro_recurso.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = public.periodo.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.planificacion by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionBy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = public.planificacion.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorABy($where, $fields = "*", $asobjects = false, $params = null){
               $sql = "SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = public.profesor.id WHERE $where;";
               if (is_array($params)) $r = $this->getAnythingParams($sql, $params, $asobjects);
               else $r = $this->getAnything($sql, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.asignatura, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.asignatura.id,public.asignatura.descripcion,public.asignatura.especialidad,public.asignatura.nivel,public.asignatura.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = public.asignatura.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.brigada.id,public.brigada.descripcion,public.brigada.especialidad,public.brigada.nivel,public.brigada.matricula,public.brigada.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = public.brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.clasificacion_de_actividad.id,public.clasificacion_de_actividad.descripcion,public.clasificacion_de_actividad.duracion,public.clasificacion_de_actividad.creador,public.clasificacion_de_actividad.prioridad,public.clasificacion_de_actividad.gasto";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = public.clasificacion_de_actividad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.brigada.id,public.brigada.descripcion,public.brigada.especialidad,public.brigada.nivel,public.brigada.matricula,public.brigada.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = public.brigada.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.especialidad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.especialidad.id,public.especialidad.descripcion,public.especialidad.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = public.especialidad.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.lugar.id,public.lugar.nombre,public.lugar.capacidad,public.lugar.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = public.lugar.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.nivel, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.nivel.id,public.nivel.descripcion,public.nivel.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = public.nivel.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.otro_recurso.id,public.otro_recurso.nombre,public.otro_recurso.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = public.otro_recurso.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.periodo.id,public.periodo.descripcion,public.periodo.cant_dias,public.periodo.cant_turnos,public.periodo.hora_inicial,public.periodo.duracion_del_turno,public.periodo.planificacion";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = public.periodo.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.planificacion, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.planificacion.id,public.planificacion.descripcion,public.planificacion.fecha_inicial,public.planificacion.cant_dias,public.planificacion.activa,public.planificacion.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = public.planificacion.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorAByPhrase($phrase, $where="true", $fields="*", $asobjects = false, $params = null){
               if ($fields == "*") $fields = "restriccion_condicional.id,restriccion_condicional.planificacion,restriccion_condicional.activa,restriccion_condicional.clasif_act,restriccion_condicional.asig,restriccion_condicional.brigada,restriccion_condicional.profesor,restriccion_condicional.lugar,restriccion_condicional.otro_recurso,restriccion_condicional.especialidad,restriccion_condicional.nivel,restriccion_condicional.fecha_ini,restriccion_condicional.fecha_fin,restriccion_condicional.hora_ini,restriccion_condicional.hora_fin,restriccion_condicional.discreto_exacto,restriccion_condicional.dia_ini_planif,restriccion_condicional.dia_fin_planif,restriccion_condicional.hora_ini_planif,restriccion_condicional.hora_fin_planif,restriccion_condicional.discreto_exacto_planif,restriccion_condicional.periodo,restriccion_condicional.dia_ini,restriccion_condicional.dia_fin,restriccion_condicional.turno_ini,restriccion_condicional.turno_fin,restriccion_condicional.discreto,restriccion_condicional.hoy,restriccion_condicional.ahora,restriccion_condicional.dia_relativo_anterior,restriccion_condicional.cant_dias_relativo_anterior,restriccion_condicional.dia_relativo_posterior,restriccion_condicional.cant_dias_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_anterior_ini,restriccion_condicional.rango_tiempo_relativo_anterior_tamano,restriccion_condicional.turno_relativo_anterior,restriccion_condicional.cant_turnos_relativo_anterior,restriccion_condicional.turno_relativo_posterior,restriccion_condicional.cant_turnos_relativo_posterior,restriccion_condicional.rango_tiempo_relativo_posterior_ini,restriccion_condicional.rango_tiempo_relativo_posterior_tamano,restriccion_condicional.entre_dias_antes,restriccion_condicional.entre_dias_despues,restriccion_condicional.entre_turnos_antes,restriccion_condicional.entre_turnos_despues,restriccion_condicional.entre_tiempo_antes,restriccion_condicional.entre_tiempo_despues,restriccion_condicional.cant_exa,restriccion_condicional.cant_min,restriccion_condicional.cant_max,restriccion_condicional.consec_brigada,restriccion_condicional.consec_profesor,restriccion_condicional.consec_lugar,restriccion_condicional.consec_otro_recurso,restriccion_condicional.consec_periodo,restriccion_condicional.consec_dia_ini,restriccion_condicional.consec_dia_fin,restriccion_condicional.consec_turno_ini,restriccion_condicional.consec_turno_fin,restriccion_condicional.consec_discreto,restriccion_condicional.siempre,restriccion_condicional.valor,public.profesor.id,public.profesor.nombre,public.profesor.creador";
               $params = is_array($params) ? array_values($params) : array();
               $phrasepart = $this->getWHEREPartOfSearchFrase($phrase, $fields, $params);
               $r = $this->getAnythingParams("SELECT ".PartModel::sqlSelectList($fields)." FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = public.profesor.id WHERE ($where) AND ($phrasepart);", $params, $asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
    } 
?>