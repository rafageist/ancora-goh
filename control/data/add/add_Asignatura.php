<?php 

/** 
  * @name: Receptor of Asignatura data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.AsignaturaModel"); 
import("model.data.Asignatura"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Asignatura_descripcion.php")) include "../control/data/fdms/fdm_Asignatura_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_especialidad.php")) include "../control/data/fdms/fdm_Asignatura_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_nivel.php")) include "../control/data/fdms/fdm_Asignatura_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_creador.php")) include "../control/data/fdms/fdm_Asignatura_creador.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Asignatura_descripcion"];
   $especialidad = $_POST["field_Asignatura_especialidad"];
   $nivel = $_POST["field_Asignatura_nivel"];
   $creador = $_POST["field_Asignatura_creador"];

 // Construyendo el objeto 
   $obj = new Asignatura($descripcion,$especialidad,$nivel,$creador,"",true, null, false);
?>