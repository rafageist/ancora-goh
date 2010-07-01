<?php 

/** 
  * @name: Receptor of Brigada data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.BrigadaModel"); 
import("model.data.Brigada"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Brigada_descripcion.php")) include "../control/data/fdms/fdm_Brigada_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Brigada_especialidad.php")) include "../control/data/fdms/fdm_Brigada_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Brigada_nivel.php")) include "../control/data/fdms/fdm_Brigada_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_Brigada_matricula.php")) include "../control/data/fdms/fdm_Brigada_matricula.php"; 
   if (file_exists("../control/data/fdms/fdm_Brigada_creador.php")) include "../control/data/fdms/fdm_Brigada_creador.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Brigada_descripcion"];
   $especialidad = $_POST["field_Brigada_especialidad"];
   $nivel = $_POST["field_Brigada_nivel"];
   $matricula = $_POST["field_Brigada_matricula"];
   $creador = $_POST["field_Brigada_creador"];

 // Construyendo el objeto 
   $obj = new Brigada($descripcion,$especialidad,$nivel,$matricula,$creador,"",true, null, false);
?>