<?php 

/** 
  * @name: Receptor of HigieneParaProfesor data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.HigieneParaProfesorModel"); 
import("model.data.HigieneParaProfesor"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_periodo.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_profesor.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_HigieneParaProfesor_gasto_maximo_diario.php")) include "../control/data/fdms/fdm_HigieneParaProfesor_gasto_maximo_diario.php"; 

// Getting data from POST 
   $periodo = $_POST["field_HigieneParaProfesor_periodo"];
   $profesor = $_POST["field_HigieneParaProfesor_profesor"];
   $gasto_maximo_diario = $_POST["field_HigieneParaProfesor_gasto_maximo_diario"];

 // Construyendo el objeto 
   $obj = new HigieneParaProfesor($periodo,$profesor,$gasto_maximo_diario,true, null, false);
?>