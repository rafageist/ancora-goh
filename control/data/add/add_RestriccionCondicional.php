<?php 

/** 
  * @name: Receptor of RestriccionCondicional data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.RestriccionCondicionalModel"); 
import("model.data.RestriccionCondicional"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_planificacion.php")) include "../control/data/fdms/fdm_RestriccionCondicional_planificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_activa.php")) include "../control/data/fdms/fdm_RestriccionCondicional_activa.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_clasif_act.php")) include "../control/data/fdms/fdm_RestriccionCondicional_clasif_act.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_asig.php")) include "../control/data/fdms/fdm_RestriccionCondicional_asig.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_brigada.php")) include "../control/data/fdms/fdm_RestriccionCondicional_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_profesor.php")) include "../control/data/fdms/fdm_RestriccionCondicional_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_lugar.php")) include "../control/data/fdms/fdm_RestriccionCondicional_lugar.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_otro_recurso.php")) include "../control/data/fdms/fdm_RestriccionCondicional_otro_recurso.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_especialidad.php")) include "../control/data/fdms/fdm_RestriccionCondicional_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_nivel.php")) include "../control/data/fdms/fdm_RestriccionCondicional_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_fecha_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_fecha_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_fecha_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_fecha_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_hora_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_hora_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_hora_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_hora_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_discreto_exacto.php")) include "../control/data/fdms/fdm_RestriccionCondicional_discreto_exacto.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_ini_planif.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_ini_planif.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_fin_planif.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_fin_planif.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_hora_ini_planif.php")) include "../control/data/fdms/fdm_RestriccionCondicional_hora_ini_planif.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_hora_fin_planif.php")) include "../control/data/fdms/fdm_RestriccionCondicional_hora_fin_planif.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_discreto_exacto_planif.php")) include "../control/data/fdms/fdm_RestriccionCondicional_discreto_exacto_planif.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_periodo.php")) include "../control/data/fdms/fdm_RestriccionCondicional_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_turno_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_turno_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_turno_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_turno_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_discreto.php")) include "../control/data/fdms/fdm_RestriccionCondicional_discreto.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_hoy.php")) include "../control/data/fdms/fdm_RestriccionCondicional_hoy.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_ahora.php")) include "../control/data/fdms/fdm_RestriccionCondicional_ahora.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_relativo_anterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_relativo_anterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_dias_relativo_anterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_dias_relativo_anterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_dia_relativo_posterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_dia_relativo_posterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_dias_relativo_posterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_dias_relativo_posterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_anterior_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_anterior_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_anterior_tamano.php")) include "../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_anterior_tamano.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_turno_relativo_anterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_turno_relativo_anterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_turnos_relativo_anterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_turnos_relativo_anterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_turno_relativo_posterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_turno_relativo_posterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_turnos_relativo_posterior.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_turnos_relativo_posterior.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_posterior_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_posterior_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_posterior_tamano.php")) include "../control/data/fdms/fdm_RestriccionCondicional_rango_tiempo_relativo_posterior_tamano.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_dias_antes.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_dias_antes.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_dias_despues.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_dias_despues.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_turnos_antes.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_turnos_antes.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_turnos_despues.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_turnos_despues.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_tiempo_antes.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_tiempo_antes.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_entre_tiempo_despues.php")) include "../control/data/fdms/fdm_RestriccionCondicional_entre_tiempo_despues.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_exa.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_exa.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_min.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_min.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_cant_max.php")) include "../control/data/fdms/fdm_RestriccionCondicional_cant_max.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_brigada.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_profesor.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_lugar.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_lugar.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_otro_recurso.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_otro_recurso.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_periodo.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_dia_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_dia_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_dia_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_dia_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_turno_ini.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_turno_ini.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_turno_fin.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_turno_fin.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_consec_discreto.php")) include "../control/data/fdms/fdm_RestriccionCondicional_consec_discreto.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_siempre.php")) include "../control/data/fdms/fdm_RestriccionCondicional_siempre.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionCondicional_valor.php")) include "../control/data/fdms/fdm_RestriccionCondicional_valor.php"; 

// Getting data from POST 
   $planificacion = $_POST["field_RestriccionCondicional_planificacion"];
   $activa = $_POST["field_RestriccionCondicional_activa"];
   $clasif_act = $_POST["field_RestriccionCondicional_clasif_act"];
   $asig = $_POST["field_RestriccionCondicional_asig"];
   $brigada = $_POST["field_RestriccionCondicional_brigada"];
   $profesor = $_POST["field_RestriccionCondicional_profesor"];
   $lugar = $_POST["field_RestriccionCondicional_lugar"];
   $otro_recurso = $_POST["field_RestriccionCondicional_otro_recurso"];
   $especialidad = $_POST["field_RestriccionCondicional_especialidad"];
   $nivel = $_POST["field_RestriccionCondicional_nivel"];
   $fecha_ini = $_POST["field_RestriccionCondicional_fecha_ini"];
   $fecha_fin = $_POST["field_RestriccionCondicional_fecha_fin"];
   $hora_ini = $_POST["field_RestriccionCondicional_hora_ini"];
   $hora_fin = $_POST["field_RestriccionCondicional_hora_fin"];
   $discreto_exacto = $_POST["field_RestriccionCondicional_discreto_exacto"];
   $dia_ini_planif = $_POST["field_RestriccionCondicional_dia_ini_planif"];
   $dia_fin_planif = $_POST["field_RestriccionCondicional_dia_fin_planif"];
   $hora_ini_planif = $_POST["field_RestriccionCondicional_hora_ini_planif"];
   $hora_fin_planif = $_POST["field_RestriccionCondicional_hora_fin_planif"];
   $discreto_exacto_planif = $_POST["field_RestriccionCondicional_discreto_exacto_planif"];
   $periodo = $_POST["field_RestriccionCondicional_periodo"];
   $dia_ini = $_POST["field_RestriccionCondicional_dia_ini"];
   $dia_fin = $_POST["field_RestriccionCondicional_dia_fin"];
   $turno_ini = $_POST["field_RestriccionCondicional_turno_ini"];
   $turno_fin = $_POST["field_RestriccionCondicional_turno_fin"];
   $discreto = $_POST["field_RestriccionCondicional_discreto"];
   $hoy = $_POST["field_RestriccionCondicional_hoy"];
   $ahora = $_POST["field_RestriccionCondicional_ahora"];
   $dia_relativo_anterior = $_POST["field_RestriccionCondicional_dia_relativo_anterior"];
   $cant_dias_relativo_anterior = $_POST["field_RestriccionCondicional_cant_dias_relativo_anterior"];
   $dia_relativo_posterior = $_POST["field_RestriccionCondicional_dia_relativo_posterior"];
   $cant_dias_relativo_posterior = $_POST["field_RestriccionCondicional_cant_dias_relativo_posterior"];
   $rango_tiempo_relativo_anterior_ini = $_POST["field_RestriccionCondicional_rango_tiempo_relativo_anterior_ini"];
   $rango_tiempo_relativo_anterior_tamano = $_POST["field_RestriccionCondicional_rango_tiempo_relativo_anterior_tamano"];
   $turno_relativo_anterior = $_POST["field_RestriccionCondicional_turno_relativo_anterior"];
   $cant_turnos_relativo_anterior = $_POST["field_RestriccionCondicional_cant_turnos_relativo_anterior"];
   $turno_relativo_posterior = $_POST["field_RestriccionCondicional_turno_relativo_posterior"];
   $cant_turnos_relativo_posterior = $_POST["field_RestriccionCondicional_cant_turnos_relativo_posterior"];
   $rango_tiempo_relativo_posterior_ini = $_POST["field_RestriccionCondicional_rango_tiempo_relativo_posterior_ini"];
   $rango_tiempo_relativo_posterior_tamano = $_POST["field_RestriccionCondicional_rango_tiempo_relativo_posterior_tamano"];
   $entre_dias_antes = $_POST["field_RestriccionCondicional_entre_dias_antes"];
   $entre_dias_despues = $_POST["field_RestriccionCondicional_entre_dias_despues"];
   $entre_turnos_antes = $_POST["field_RestriccionCondicional_entre_turnos_antes"];
   $entre_turnos_despues = $_POST["field_RestriccionCondicional_entre_turnos_despues"];
   $entre_tiempo_antes = $_POST["field_RestriccionCondicional_entre_tiempo_antes"];
   $entre_tiempo_despues = $_POST["field_RestriccionCondicional_entre_tiempo_despues"];
   $cant_exa = $_POST["field_RestriccionCondicional_cant_exa"];
   $cant_min = $_POST["field_RestriccionCondicional_cant_min"];
   $cant_max = $_POST["field_RestriccionCondicional_cant_max"];
   $consec_brigada = $_POST["field_RestriccionCondicional_consec_brigada"];
   $consec_profesor = $_POST["field_RestriccionCondicional_consec_profesor"];
   $consec_lugar = $_POST["field_RestriccionCondicional_consec_lugar"];
   $consec_otro_recurso = $_POST["field_RestriccionCondicional_consec_otro_recurso"];
   $consec_periodo = $_POST["field_RestriccionCondicional_consec_periodo"];
   $consec_dia_ini = $_POST["field_RestriccionCondicional_consec_dia_ini"];
   $consec_dia_fin = $_POST["field_RestriccionCondicional_consec_dia_fin"];
   $consec_turno_ini = $_POST["field_RestriccionCondicional_consec_turno_ini"];
   $consec_turno_fin = $_POST["field_RestriccionCondicional_consec_turno_fin"];
   $consec_discreto = $_POST["field_RestriccionCondicional_consec_discreto"];
   $siempre = $_POST["field_RestriccionCondicional_siempre"];
   $valor = $_POST["field_RestriccionCondicional_valor"];

 // Construyendo el objeto 
   $obj = new RestriccionCondicional($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,"",true, null, false);
?>