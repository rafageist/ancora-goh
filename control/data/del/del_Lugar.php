<?php 

/** 
  * @name: Receptor of Lugar identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.LugarModel"); 
import("model.data.Lugar"); 
import("model.data.LugarCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Lugar_nombre.php")) include "../control/data/fdms/fdm_Lugar_nombre.php"; 
   if (file_exists("../control/data/fdms/fdm_Lugar_capacidad.php")) include "../control/data/fdms/fdm_Lugar_capacidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Lugar_creador.php")) include "../control/data/fdms/fdm_Lugar_creador.php"; 
   if (isset($_POST["field_Lugar_id"])) $id = $_POST["field_Lugar_id"]; else $id = null;
   if (isset($_POST["field_Lugar_nombre"])) $nombre = $_POST["field_Lugar_nombre"]; else $nombre = null;
   if (isset($_POST["field_Lugar_capacidad"])) $capacidad = $_POST["field_Lugar_capacidad"]; else $capacidad = null;
   if (isset($_POST["field_Lugar_creador"])) $creador = $_POST["field_Lugar_creador"]; else $creador = null;

 // Eliminando el objeto 
   $col = new LugarCollection($dbconnection);
   $col->removeItem($id);
?>