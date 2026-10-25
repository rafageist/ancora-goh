<?php 

/** 
  * @name: Receptor of Nivel identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.NivelModel"); 
import("model.data.Nivel"); 
import("model.data.NivelCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Nivel_descripcion.php")) include "../control/data/fdms/fdm_Nivel_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Nivel_creador.php")) include "../control/data/fdms/fdm_Nivel_creador.php"; 
   if (isset($_POST["field_Nivel_id"])) $id = $_POST["field_Nivel_id"]; else $id = null;
   if (isset($_POST["field_Nivel_descripcion"])) $descripcion = $_POST["field_Nivel_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Nivel_creador"])) $creador = $_POST["field_Nivel_creador"]; else $creador = null;

 // Eliminando el objeto 
   $col = new NivelCollection($dbconnection);
   $col->removeItem($id);
?>