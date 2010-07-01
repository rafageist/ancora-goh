<?php 

/** 
  * @name: Receptor of ClasificacionDeActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.ClasificacionDeActividadModel"); 
import("model.data.ClasificacionDeActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_descripcion.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_duracion.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_duracion.php"; 
   if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_creador.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_creador.php"; 
   if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_prioridad.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_prioridad.php"; 
   if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_gasto.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_gasto.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_ClasificacionDeActividad_descripcion"];
   $duracion = $_POST["field_ClasificacionDeActividad_duracion"];
   $creador = $_POST["field_ClasificacionDeActividad_creador"];
   $prioridad = $_POST["field_ClasificacionDeActividad_prioridad"];
   $gasto = $_POST["field_ClasificacionDeActividad_gasto"];

 // Construyendo el objeto 
   $obj = new ClasificacionDeActividad($descripcion,$duracion,$creador,$prioridad,$gasto,"",true, null, false);
?>