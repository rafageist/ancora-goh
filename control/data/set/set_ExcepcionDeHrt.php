<?php 

/** 
  * @name: Receptor of ExcepcionDeHrt data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.ExcepcionDeHrtModel"); 
import("model.data.ExcepcionDeHrt"); 
import("model.data.ExcepcionDeHrtCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_herencia.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_herencia.php"; 
if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_periodo.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_dia.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_dia.php"; 
if (file_exists("../control/data/fdms/fdm_ExcepcionDeHrt_turno.php")) include "../control/data/fdms/fdm_ExcepcionDeHrt_turno.php"; 

// Getting data from POST 
   if (isset($_POST["field_ExcepcionDeHrt_herencia"])) $herencia = $_POST["field_ExcepcionDeHrt_herencia"]; else $herencia = "null";
   if (isset($_POST["field_ExcepcionDeHrt_periodo"])) $periodo = $_POST["field_ExcepcionDeHrt_periodo"]; else $periodo = "null";
   if (isset($_POST["field_ExcepcionDeHrt_dia"])) $dia = $_POST["field_ExcepcionDeHrt_dia"]; else $dia = "null";
   if (isset($_POST["field_ExcepcionDeHrt_turno"])) $turno = $_POST["field_ExcepcionDeHrt_turno"]; else $turno = "null";

 // Actualizando el objeto 
   $col = new ExcepcionDeHrtCollection($dbconnection);
   $obj = $col->getItem($herencia,$periodo,$dia,$turno);
$obj->setHerencia($herencia);
$obj->setPeriodo($periodo);
$obj->setDia($dia);
$obj->setTurno($turno);
?>