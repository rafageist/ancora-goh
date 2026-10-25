<?php 

/** 
  * @name: Receptor of Grupo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.GrupoModel"); 
import("model.data.Grupo"); 
import("model.data.GrupoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Grupo_clasificacion.php")) include "../control/data/fdms/fdm_Grupo_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_descripcion.php")) include "../control/data/fdms/fdm_Grupo_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_especialidad.php")) include "../control/data/fdms/fdm_Grupo_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_nivel.php")) include "../control/data/fdms/fdm_Grupo_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_Grupo_planificacion.php")) include "../control/data/fdms/fdm_Grupo_planificacion.php"; 
   if (isset($_POST["field_Grupo_id"])) $id = $_POST["field_Grupo_id"]; else $id = null;
   if (isset($_POST["field_Grupo_clasificacion"])) $clasificacion = $_POST["field_Grupo_clasificacion"]; else $clasificacion = null;
   if (isset($_POST["field_Grupo_descripcion"])) $descripcion = $_POST["field_Grupo_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Grupo_especialidad"])) $especialidad = $_POST["field_Grupo_especialidad"]; else $especialidad = null;
   if (isset($_POST["field_Grupo_nivel"])) $nivel = $_POST["field_Grupo_nivel"]; else $nivel = null;
   if (isset($_POST["field_Grupo_planificacion"])) $planificacion = $_POST["field_Grupo_planificacion"]; else $planificacion = null;

 // Eliminando el objeto 
   $col = new GrupoCollection($dbconnection);
   $col->removeItem($id);
?>