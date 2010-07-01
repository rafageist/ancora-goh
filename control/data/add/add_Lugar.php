<?php 

/** 
  * @name: Receptor of Lugar data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.LugarModel"); 
import("model.data.Lugar"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_Lugar_nombre.php")) include "../control/data/fdms/fdm_Lugar_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Lugar_capacidad.php")) include "../control/data/fdms/fdm_Lugar_capacidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Lugar_creador.php")) include "../control/data/fdms/fdm_Lugar_creador.php"; 

// Getting data from POST 
   $nombre = $_POST["field_Lugar_nombre"];
   $capacidad = $_POST["field_Lugar_capacidad"];
   $creador = $_POST["field_Lugar_creador"];

 // Construyendo el objeto 
   $obj = new Lugar($nombre,$capacidad,$creador,"",true, null, false);
?>