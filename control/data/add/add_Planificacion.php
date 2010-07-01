<?php 

/** 
  * @name: Receptor of Planificacion data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.PlanificacionModel"); 
import("model.data.Planificacion"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Planificacion_descripcion.php")) include "../control/data/fdms/fdm_Planificacion_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_fecha_inicial.php")) include "../control/data/fdms/fdm_Planificacion_fecha_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_cant_dias.php")) include "../control/data/fdms/fdm_Planificacion_cant_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_activa.php")) include "../control/data/fdms/fdm_Planificacion_activa.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_creador.php")) include "../control/data/fdms/fdm_Planificacion_creador.php"; 

// Getting data from POST 
   $descripcion = $_POST["field_Planificacion_descripcion"];
   $fecha_inicial = $_POST["field_Planificacion_fecha_inicial"];
   $cant_dias = $_POST["field_Planificacion_cant_dias"];
   $activa = $_POST["field_Planificacion_activa"];
   $creador = $_POST["field_Planificacion_creador"];

 // Construyendo el objeto 
   $obj = new Planificacion($descripcion,$fecha_inicial,$cant_dias,$activa,$creador,"",true, null, false);
?>