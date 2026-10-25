<?php 

/** 
  * @name: Receptor of PlantillaDeActividad identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.PlantillaDeActividadModel"); 
import("model.data.PlantillaDeActividad"); 
import("model.data.PlantillaDeActividadCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_desglose.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_desglose.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_orden.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_orden.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_clasificacion.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_exige_profesor.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_exige_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_exige_lugar.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_exige_lugar.php"; 
   if (isset($_POST["field_PlantillaDeActividad_id"])) $id = $_POST["field_PlantillaDeActividad_id"]; else $id = null;
   if (isset($_POST["field_PlantillaDeActividad_desglose"])) $desglose = $_POST["field_PlantillaDeActividad_desglose"]; else $desglose = null;
   if (isset($_POST["field_PlantillaDeActividad_orden"])) $orden = $_POST["field_PlantillaDeActividad_orden"]; else $orden = null;
   if (isset($_POST["field_PlantillaDeActividad_clasificacion"])) $clasificacion = $_POST["field_PlantillaDeActividad_clasificacion"]; else $clasificacion = null;
   if (isset($_POST["field_PlantillaDeActividad_exige_profesor"])) $exige_profesor = $_POST["field_PlantillaDeActividad_exige_profesor"]; else $exige_profesor = null;
   if (isset($_POST["field_PlantillaDeActividad_exige_lugar"])) $exige_lugar = $_POST["field_PlantillaDeActividad_exige_lugar"]; else $exige_lugar = null;

 // Eliminando el objeto 
   $col = new PlantillaDeActividadCollection($dbconnection);
   $col->removeItem($id);
?>