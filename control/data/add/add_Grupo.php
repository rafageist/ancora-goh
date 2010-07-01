<?php 

/** 
  * @name: Receptor of Grupo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.GrupoModel"); 
import("model.data.Grupo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Grupo_clasificacion.php")) include "../control/data/fdms/fdm_Grupo_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_descripcion.php")) include "../control/data/fdms/fdm_Grupo_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_especialidad.php")) include "../control/data/fdms/fdm_Grupo_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_nivel.php")) include "../control/data/fdms/fdm_Grupo_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_planificacion.php")) include "../control/data/fdms/fdm_Grupo_planificacion.php"; 

// Getting data from POST 
   $clasificacion = $_POST["field_Grupo_clasificacion"];
   $descripcion = $_POST["field_Grupo_descripcion"];
   $especialidad = $_POST["field_Grupo_especialidad"];
   $nivel = $_POST["field_Grupo_nivel"];
   $planificacion = $_POST["field_Grupo_planificacion"];

 // Construyendo el objeto 
   $obj = new Grupo($clasificacion,$descripcion,$especialidad,$nivel,$planificacion,"",true, null, false);
?>