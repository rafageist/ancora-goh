<?php 

/** 
  * @name: Receptor of Asignatura identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.AsignaturaModel"); 
import("model.data.Asignatura"); 
import("model.data.AsignaturaCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Asignatura_descripcion.php")) include "../control/data/fdms/fdm_Asignatura_descripcion.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_especialidad.php")) include "../control/data/fdms/fdm_Asignatura_especialidad.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_nivel.php")) include "../control/data/fdms/fdm_Asignatura_nivel.php"; 
   if (file_exists("../control/data/fdms/fdm_Asignatura_creador.php")) include "../control/data/fdms/fdm_Asignatura_creador.php"; 
   if (isset($_POST["field_Asignatura_id"])) $id = $_POST["field_Asignatura_id"]; else $id = "null";
   if (isset($_POST["field_Asignatura_descripcion"])) $descripcion = $_POST["field_Asignatura_descripcion"]; else $descripcion = "null";
   if (isset($_POST["field_Asignatura_especialidad"])) $especialidad = $_POST["field_Asignatura_especialidad"]; else $especialidad = "null";
   if (isset($_POST["field_Asignatura_nivel"])) $nivel = $_POST["field_Asignatura_nivel"]; else $nivel = "null";
   if (isset($_POST["field_Asignatura_creador"])) $creador = $_POST["field_Asignatura_creador"]; else $creador = "null";

 // Eliminando el objeto 
   $col = new AsignaturaCollection($dbconnection);
   $col->removeItem($id);
?>