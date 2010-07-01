<?php 

/** 
  * @name: Receptor of PlantillaDeActividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:15 
  * @version: 1.0 
  */ 
import("model.data.PlantillaDeActividadModel"); 
import("model.data.PlantillaDeActividad"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_desglose.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_desglose.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_orden.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_orden.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_clasificacion.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_exige_profesor.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_exige_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_PlantillaDeActividad_exige_lugar.php")) include "../control/data/fdms/fdm_PlantillaDeActividad_exige_lugar.php"; 

// Getting data from POST 
   $desglose = $_POST["field_PlantillaDeActividad_desglose"];
   $orden = $_POST["field_PlantillaDeActividad_orden"];
   $clasificacion = $_POST["field_PlantillaDeActividad_clasificacion"];
   $exige_profesor = $_POST["field_PlantillaDeActividad_exige_profesor"];
   $exige_lugar = $_POST["field_PlantillaDeActividad_exige_lugar"];

 // Construyendo el objeto 
   $obj = new PlantillaDeActividad($desglose,$orden,$clasificacion,$exige_profesor,$exige_lugar,"",true, null, false);
?>