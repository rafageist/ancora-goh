<?php 

/** 
  * @name: Receptor of HigieneParaBrigada data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.HigieneParaBrigadaModel"); 
import("model.data.HigieneParaBrigada"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_periodo.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_brigada.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_HigieneParaBrigada_gasto_maximo_diario.php")) include "../control/data/fdms/fdm_HigieneParaBrigada_gasto_maximo_diario.php"; 

// Getting data from POST 
   $periodo = $_POST["field_HigieneParaBrigada_periodo"];
   $brigada = $_POST["field_HigieneParaBrigada_brigada"];
   $gasto_maximo_diario = $_POST["field_HigieneParaBrigada_gasto_maximo_diario"];

 // Construyendo el objeto 
   $obj = new HigieneParaBrigada($periodo,$brigada,$gasto_maximo_diario,true, null, false);
?>