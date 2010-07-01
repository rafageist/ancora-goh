<?php 

/** 
  * @name: Receptor of HigieneParaBrigada data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.HigieneParaBrigadaModel"); 
import("model.data.HigieneParaBrigada"); 
import("model.data.HigieneParaBrigadaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_periodo.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_brigada.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_brigada.php"; 
if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_gasto_maximo_diario.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_gasto_maximo_diario.php"; 

// Getting data from POST 
   if (isset($_POST["field_HigieneParaBrigada_periodo"])) $periodo = $_POST["field_HigieneParaBrigada_periodo"]; else $periodo = "null";
   if (isset($_POST["field_HigieneParaBrigada_brigada"])) $brigada = $_POST["field_HigieneParaBrigada_brigada"]; else $brigada = "null";
   if (isset($_POST["field_HigieneParaBrigada_gasto_maximo_diario"])) $gasto_maximo_diario = $_POST["field_HigieneParaBrigada_gasto_maximo_diario"]; else $gasto_maximo_diario = "null";

 // Actualizando el objeto 
   $col = new HigieneParaBrigadaCollection($dbconnection);
   $obj = $col->getItem($periodo,$brigada,$gasto_maximo_diario);
$obj->setPeriodo($periodo);
$obj->setBrigada($brigada);
$obj->setGastoMaximoDiario($gasto_maximo_diario);
?>