<?php 

/** 
  * @name: Receptor of ClasificacionDeActividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.ClasificacionDeActividadModel"); 
import("model.data.ClasificacionDeActividad"); 
import("model.data.ClasificacionDeActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_descripcion.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_duracion.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_duracion.php"; 
if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_creador.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_creador.php"; 
if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_prioridad.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_prioridad.php"; 
if (file_exists("../control/data/fdms/fdm_ClasificacionDeActividad_gasto.php")) include "../control/data/fdms/fdm_ClasificacionDeActividad_gasto.php"; 

// Getting data from POST 
   if (isset($_POST["field_ClasificacionDeActividad_id"])) $id = $_POST["field_ClasificacionDeActividad_id"]; else $id = "null";
   if (isset($_POST["field_ClasificacionDeActividad_descripcion"])) $descripcion = $_POST["field_ClasificacionDeActividad_descripcion"]; else $descripcion = "null";
   if (isset($_POST["field_ClasificacionDeActividad_duracion"])) $duracion = $_POST["field_ClasificacionDeActividad_duracion"]; else $duracion = "null";
   if (isset($_POST["field_ClasificacionDeActividad_creador"])) $creador = $_POST["field_ClasificacionDeActividad_creador"]; else $creador = "null";
   if (isset($_POST["field_ClasificacionDeActividad_prioridad"])) $prioridad = $_POST["field_ClasificacionDeActividad_prioridad"]; else $prioridad = "null";
   if (isset($_POST["field_ClasificacionDeActividad_gasto"])) $gasto = $_POST["field_ClasificacionDeActividad_gasto"]; else $gasto = "null";

 // Actualizando el objeto 
   $col = new ClasificacionDeActividadCollection($dbconnection);
   $obj = $col->getItem($id);
$obj->setDescripcion($descripcion);
$obj->setDuracion($duracion);
$obj->setCreador($creador);
$obj->setPrioridad($prioridad);
$obj->setGasto($gasto);
?>