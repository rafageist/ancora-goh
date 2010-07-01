<?php 
   class RestriccionCondicionalModel extends PartModel { 
 
          public function RestriccionCondicionalModel($db){ 
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
               if ($activa == true || $activa == 1 || $activa == "t") $activa = "true"; 
               else if ($activa== true || $activa == 0 || $activa == "f") $activa = "false"; 
               if ($discreto_exacto == true || $discreto_exacto == 1 || $discreto_exacto == "t") $discreto_exacto = "true"; 
               else if ($discreto_exacto== true || $discreto_exacto == 0 || $discreto_exacto == "f") $discreto_exacto = "false"; 
               if ($discreto_exacto_planif == true || $discreto_exacto_planif == 1 || $discreto_exacto_planif == "t") $discreto_exacto_planif = "true"; 
               else if ($discreto_exacto_planif== true || $discreto_exacto_planif == 0 || $discreto_exacto_planif == "f") $discreto_exacto_planif = "false"; 
               if ($discreto == true || $discreto == 1 || $discreto == "t") $discreto = "true"; 
               else if ($discreto== true || $discreto == 0 || $discreto == "f") $discreto = "false"; 
               if ($hoy == true || $hoy == 1 || $hoy == "t") $hoy = "true"; 
               else if ($hoy== true || $hoy == 0 || $hoy == "f") $hoy = "false"; 
               if ($ahora == true || $ahora == 1 || $ahora == "t") $ahora = "true"; 
               else if ($ahora== true || $ahora == 0 || $ahora == "f") $ahora = "false"; 
               if ($consec_discreto == true || $consec_discreto == 1 || $consec_discreto == "t") $consec_discreto = "true"; 
               else if ($consec_discreto== true || $consec_discreto == 0 || $consec_discreto == "f") $consec_discreto = "false"; 
               if ($siempre == true || $siempre == 1 || $siempre == "t") $siempre = "true"; 
               else if ($siempre== true || $siempre == 0 || $siempre == "f") $siempre = "false"; 
             $r = $this->getAnything("INSERT INTO restriccion_condicional (id,planificacion,activa,clasif_act,asig,brigada,profesor,lugar,otro_recurso,especialidad,nivel,fecha_ini,fecha_fin,hora_ini,hora_fin,discreto_exacto,dia_ini_planif,dia_fin_planif,hora_ini_planif,hora_fin_planif,discreto_exacto_planif,periodo,dia_ini,dia_fin,turno_ini,turno_fin,discreto,hoy,ahora,dia_relativo_anterior,cant_dias_relativo_anterior,dia_relativo_posterior,cant_dias_relativo_posterior,rango_tiempo_relativo_anterior_ini,rango_tiempo_relativo_anterior_tamano,turno_relativo_anterior,cant_turnos_relativo_anterior,turno_relativo_posterior,cant_turnos_relativo_posterior,rango_tiempo_relativo_posterior_ini,rango_tiempo_relativo_posterior_tamano,entre_dias_antes,entre_dias_despues,entre_turnos_antes,entre_turnos_despues,entre_tiempo_antes,entre_tiempo_despues,cant_exa,cant_min,cant_max,consec_brigada,consec_profesor,consec_lugar,consec_otro_recurso,consec_periodo,consec_dia_ini,consec_dia_fin,consec_turno_ini,consec_turno_fin,consec_discreto,siempre,valor) VALUES ('$id',".(planificacion==null?'null':"'".$planificacion."'").",".(activa==null?'null':$activa).",".(clasif_act==null?'null':"'".$clasif_act."'").",".(asig==null?'null':"'".$asig."'").",".(brigada==null?'null':"'".$brigada."'").",".(profesor==null?'null':"'".$profesor."'").",".(lugar==null?'null':"'".$lugar."'").",".(otro_recurso==null?'null':"'".$otro_recurso."'").",".(especialidad==null?'null':"'".$especialidad."'").",".(nivel==null?'null':"'".$nivel."'").",".(fecha_ini==null?'null':$fecha_ini).",".(fecha_fin==null?'null':$fecha_fin).",".(hora_ini==null?'null':$hora_ini).",".(hora_fin==null?'null':$hora_fin).",".(discreto_exacto==null?'null':$discreto_exacto).",".(dia_ini_planif==null?'null':$dia_ini_planif).",".(dia_fin_planif==null?'null':$dia_fin_planif).",".(hora_ini_planif==null?'null':$hora_ini_planif).",".(hora_fin_planif==null?'null':$hora_fin_planif).",".(discreto_exacto_planif==null?'null':$discreto_exacto_planif).",".(periodo==null?'null':"'".$periodo."'").",".(dia_ini==null?'null':$dia_ini).",".(dia_fin==null?'null':$dia_fin).",".(turno_ini==null?'null':$turno_ini).",".(turno_fin==null?'null':$turno_fin).",".(discreto==null?'null':$discreto).",".(hoy==null?'null':$hoy).",".(ahora==null?'null':$ahora).",".(dia_relativo_anterior==null?'null':$dia_relativo_anterior).",".(cant_dias_relativo_anterior==null?'null':$cant_dias_relativo_anterior).",".(dia_relativo_posterior==null?'null':$dia_relativo_posterior).",".(cant_dias_relativo_posterior==null?'null':$cant_dias_relativo_posterior).",".(rango_tiempo_relativo_anterior_ini==null?'null':$rango_tiempo_relativo_anterior_ini).",".(rango_tiempo_relativo_anterior_tamano==null?'null':$rango_tiempo_relativo_anterior_tamano).",".(turno_relativo_anterior==null?'null':$turno_relativo_anterior).",".(cant_turnos_relativo_anterior==null?'null':$cant_turnos_relativo_anterior).",".(turno_relativo_posterior==null?'null':$turno_relativo_posterior).",".(cant_turnos_relativo_posterior==null?'null':$cant_turnos_relativo_posterior).",".(rango_tiempo_relativo_posterior_ini==null?'null':$rango_tiempo_relativo_posterior_ini).",".(rango_tiempo_relativo_posterior_tamano==null?'null':$rango_tiempo_relativo_posterior_tamano).",".(entre_dias_antes==null?'null':$entre_dias_antes).",".(entre_dias_despues==null?'null':$entre_dias_despues).",".(entre_turnos_antes==null?'null':$entre_turnos_antes).",".(entre_turnos_despues==null?'null':$entre_turnos_despues).",".(entre_tiempo_antes==null?'null':$entre_tiempo_antes).",".(entre_tiempo_despues==null?'null':$entre_tiempo_despues).",".(cant_exa==null?'null':$cant_exa).",".(cant_min==null?'null':$cant_min).",".(cant_max==null?'null':$cant_max).",".(consec_brigada==null?'null':"'".$consec_brigada."'").",".(consec_profesor==null?'null':"'".$consec_profesor."'").",".(consec_lugar==null?'null':"'".$consec_lugar."'").",".(consec_otro_recurso==null?'null':"'".$consec_otro_recurso."'").",".(consec_periodo==null?'null':"'".$consec_periodo."'").",".(consec_dia_ini==null?'null':$consec_dia_ini).",".(consec_dia_fin==null?'null':$consec_dia_fin).",".(consec_turno_ini==null?'null':$consec_turno_ini).",".(consec_turno_fin==null?'null':$consec_turno_fin).",".(consec_discreto==null?'null':$consec_discreto).",".(siempre==null?'null':$siempre).",".(valor==null?'null':$valor).");"); 
             return $replicas; 
            }
          /** 
            * @name: remove 
            * @desc: Remove "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function remove($id){ 
                  return $this->getAnything("DELETE FROM restriccion_condicional WHERE  id = '$id' ;");
            }
          /** 
            * @name: remove 
            * @desc: Remove 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function removeBy($where, $asobjects = false){
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
          public function set($set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_condicional SET $set;",$asobjects);
          } 
          /** 
            * @name: set 
            * @desc: Set 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function setBy($where, $set, $asobjects = false){
               return $this->getAnything("UPDATE restriccion_condicional SET $set WHERE $where;");
          } 
          /** 
            * @name: get 
            * @desc: Get specified "restriccion_condicional" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function get($id){ 
                  $r = $this->getAnything("SELECT * FROM restriccion_condicional WHERE  id = '$id' ;",true);
                   if (isset($r[0])) return $this->repairBoolean($r[0], true); else return false;
            }
          /** 
            * @name: getAll 
            * @desc: Get all 'restriccion_condicional' 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAll($fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getAllBy 
            * @desc: Get 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getAllBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional WHERE $where;",$asobjects);
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional LIMIT $limit OFFSET $offset;",$asobjects);
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
               return $r;
          } 
          /** 
            * @name: getLimitBy 
            * @desc: Limited get 'restriccion_condicional' by ... 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getLimitBy($limit, $offset, $where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional WHERE $where LIMIT $limit OFFSET $offset;", $asobjects);
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = public.asignatura.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = public.brigada.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = public.clasificacion_de_actividad.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = public.brigada.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = public.lugar.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = public.otro_recurso.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = public.periodo.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = public.profesor.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = public.especialidad.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = public.lugar.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = public.nivel.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = public.otro_recurso.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = public.periodo.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = public.planificacion.id ");
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = public.profesor.id ");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.asignatura by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = public.asignatura.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = public.brigada.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.clasificacion_de_actividad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = public.clasificacion_de_actividad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = public.brigada.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = public.lugar.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = public.otro_recurso.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = public.periodo.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = public.profesor.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.especialidad by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = public.especialidad.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = public.lugar.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.nivel by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = public.nivel.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = public.otro_recurso.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = public.periodo.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.planificacion by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionBy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = public.planificacion.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor by ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorABy($where, $fields = "*", $asobjects = false){
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = public.profesor.id  WHERE $where");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          } 
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.asignatura, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithAsignaturaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
                    $fieldsforaneas = explode($this->fieldnames["asignatura"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.asignatura ON restriccion_condicional.asig = asignatura.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.clasificacion_de_actividad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithClasificacionDeActividadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
                    $fieldsforaneas = explode($this->fieldnames["clasificacion_de_actividad"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.clasificacion_de_actividad ON restriccion_condicional.clasif_act = clasificacion_de_actividad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.brigada, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithBrigadaAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.brigada ON restriccion_condicional.consec_brigada = brigada.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
                    $fieldsforaneas = explode($this->fieldnames["lugar"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.consec_lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.consec_otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.consec_periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.consec_profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.especialidad, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithEspecialidadByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.especialidad ON restriccion_condicional.especialidad = especialidad.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.lugar, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithLugarAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
                    $fieldsforaneas = explode($this->fieldnames["lugar"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.lugar ON restriccion_condicional.lugar = lugar.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.nivel, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithNivelByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.nivel ON restriccion_condicional.nivel = nivel.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.otro_recurso, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithOtroRecursoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.otro_recurso ON restriccion_condicional.otro_recurso = otro_recurso.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.periodo, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPeriodoAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.periodo ON restriccion_condicional.periodo = periodo.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.planificacion, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithPlanificacionByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
                    $fieldsforaneas = explode($this->fieldnames["planificacion"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.planificacion ON restriccion_condicional.planificacion = planificacion.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
          /** 
            * @name: getJoin 
            * @desc: Inner join between "restriccion_condicional" and "public.profesor, by phrase ..." 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
          public function getJoinWithProfesorAByPhrase($phrase, $where="true", $fields="*", $asobjects = false){
               if ($fields == "*") {
                    $fieldstabla = explode($this->fieldnames["restriccion_condicional"]); 
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
               $r = $this->getAnything("SELECT $fields FROM restriccion_condicional INNER JOIN public.profesor ON restriccion_condicional.profesor = profesor.id  WHERE ($where) AND (".$this->getWHEREPartOfSearchFrase($phrase, $fields).")");
               if (is_array($r)) foreach($r as $key=>$item) $r[$key] = $this->repairBoolean($item, $asobjects);
             return $r; 
          }
    } 
?>