<?php 

/** 
  * @name: Receptor of ExcepcionDeHrt data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.ExcepcionDeHrtModel"); 
import("model.data.ExcepcionDeHrt"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_herencia.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_herencia.php"; 
   if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_periodo.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_dia.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_turno.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_turno.php"; 

// Getting data from POST 
   $herencia = $_POST["field_ExcepcionDeHrt_herencia"];
   $periodo = $_POST["field_ExcepcionDeHrt_periodo"];
   $dia = $_POST["field_ExcepcionDeHrt_dia"];
   $turno = $_POST["field_ExcepcionDeHrt_turno"];

 // Construyendo el objeto 
   $obj = new ExcepcionDeHrt($herencia,$periodo,$dia,$turno,true, null, false);
?>