<?php 

/** 
  * @name: Receptor of HigieneParaProfesor data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.HigieneParaProfesorModel"); 
import("model.data.HigieneParaProfesor"); 
import("model.data.HigieneParaProfesorCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_periodo.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_profesor.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_profesor.php"; 
if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_gasto_maximo_diario.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_gasto_maximo_diario.php"; 

// Getting data from POST 
   if (isset($_POST["field_HigieneParaProfesor_periodo"])) $periodo = $_POST["field_HigieneParaProfesor_periodo"]; else $periodo = "null";
   if (isset($_POST["field_HigieneParaProfesor_profesor"])) $profesor = $_POST["field_HigieneParaProfesor_profesor"]; else $profesor = "null";
   if (isset($_POST["field_HigieneParaProfesor_gasto_maximo_diario"])) $gasto_maximo_diario = $_POST["field_HigieneParaProfesor_gasto_maximo_diario"]; else $gasto_maximo_diario = "null";

 // Actualizando el objeto 
   $col = new HigieneParaProfesorCollection($dbconnection);
   $obj = $col->getItem($periodo,$profesor,$gasto_maximo_diario);
$obj->setPeriodo($periodo);
$obj->setProfesor($profesor);
$obj->setGastoMaximoDiario($gasto_maximo_diario);
?>