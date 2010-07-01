<?php 

/** 
  * @name: Receptor of Planificacion identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.PlanificacionModel"); 
import("model.data.Planificacion"); 
import("model.data.PlanificacionCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Planificacion_descripcion.php")) include "../control/data/fdms/fdm_Planificacion_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_fecha_inicial.php")) include "../control/data/fdms/fdm_Planificacion_fecha_inicial.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_cant_dias.php")) include "../control/data/fdms/fdm_Planificacion_cant_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_activa.php")) include "../control/data/fdms/fdm_Planificacion_activa.php"; 
   if (file_exists("../control/data/fdms/fdm_Planificacion_creador.php")) include "../control/data/fdms/fdm_Planificacion_creador.php"; 
   if (isset($_POST["field_Planificacion_id"])) $id = $_POST["field_Planificacion_id"]; else $id = "null";
   if (isset($_POST["field_Planificacion_descripcion"])) $descripcion = $_POST["field_Planificacion_descripcion"]; else $descripcion = "null";
   if (isset($_POST["field_Planificacion_fecha_inicial"])) $fecha_inicial = $_POST["field_Planificacion_fecha_inicial"]; else $fecha_inicial = "null";
   if (isset($_POST["field_Planificacion_cant_dias"])) $cant_dias = $_POST["field_Planificacion_cant_dias"]; else $cant_dias = "null";
   if (isset($_POST["field_Planificacion_activa"])) $activa = $_POST["field_Planificacion_activa"]; else $activa = "null";
   if (isset($_POST["field_Planificacion_creador"])) $creador = $_POST["field_Planificacion_creador"]; else $creador = "null";

 // Eliminando el objeto 
   $col = new PlanificacionCollection($dbconnection);
   $col->removeItem($id);
?>