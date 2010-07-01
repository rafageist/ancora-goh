<?php 

/** 
  * @name: Receptor of CancelarGenerador identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.CancelarGeneradorModel"); 
import("model.data.CancelarGenerador"); 
import("model.data.CancelarGeneradorCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_especialidad.php")) include "../control/data/fdms/fdm_CancelarGenerador_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_nivel.php")) include "../control/data/fdms/fdm_CancelarGenerador_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_CancelarGenerador_periodo.php")) include "../control/data/fdms/fdm_CancelarGenerador_periodo.php"; 
   if (isset($_POST["field_CancelarGenerador_especialidad"])) $especialidad = $_POST["field_CancelarGenerador_especialidad"]; else $especialidad = "null";
   if (isset($_POST["field_CancelarGenerador_nivel"])) $nivel = $_POST["field_CancelarGenerador_nivel"]; else $nivel = "null";
   if (isset($_POST["field_CancelarGenerador_periodo"])) $periodo = $_POST["field_CancelarGenerador_periodo"]; else $periodo = "null";

 // Eliminando el objeto 
   $col = new CancelarGeneradorCollection($dbconnection);
   $col->removeItem($especialidad,$nivel,$periodo);
?>