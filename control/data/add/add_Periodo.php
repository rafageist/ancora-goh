<?php 

/** 
  * @name: Receptor of Periodo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.PeriodoModel"); 
import("model.data.Periodo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Periodo_descripcion.php")) include "../control/data/fdms/fdm_Periodo_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodo_cant_dias.php")) include "../control/data/fdms/fdm_Periodo_cant_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodo_cant_turnos.php")) include "../control/data/fdms/fdm_Periodo_cant_turnos.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodo_hora_inicial.php")) include "../control/data/fdms/fdm_Periodo_hora_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodo_duracion_del_turno.php")) include "../control/data/fdms/fdm_Periodo_duracion_del_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_Periodo_planificacion.php")) include "../control/data/fdms/fdm_Periodo_planificacion.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Periodo_descripcion"];
   $cant_dias = $_POST["field_Periodo_cant_dias"];
   $cant_turnos = $_POST["field_Periodo_cant_turnos"];
   $hora_inicial = $_POST["field_Periodo_hora_inicial"];
   $duracion_del_turno = $_POST["field_Periodo_duracion_del_turno"];
   $planificacion = $_POST["field_Periodo_planificacion"];

 // Construyendo el objeto 
   $obj = new Periodo($descripcion,$cant_dias,$cant_turnos,$hora_inicial,$duracion_del_turno,$planificacion,"",true, null, false);
?>