<?php 

/** 
  * @name: Receptor of OtrosRecursosUsados identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.OtrosRecursosUsadosModel"); 
import("model.data.OtrosRecursosUsados"); 
import("model.data.OtrosRecursosUsadosCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_OtrosRecursosUsados_otro_recurso.php")) include "../control/data/fdms/fdm_OtrosRecursosUsados_otro_recurso.php"; 
   if (file_exists("../control/data/fdms/fdm_OtrosRecursosUsados_actividad.php")) include "../control/data/fdms/fdm_OtrosRecursosUsados_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_OtrosRecursosUsados_creador.php")) include "../control/data/fdms/fdm_OtrosRecursosUsados_creador.php"; 
   if (isset($_POST["field_OtrosRecursosUsados_otro_recurso"])) $otro_recurso = $_POST["field_OtrosRecursosUsados_otro_recurso"]; else $otro_recurso = "null";
   if (isset($_POST["field_OtrosRecursosUsados_actividad"])) $actividad = $_POST["field_OtrosRecursosUsados_actividad"]; else $actividad = "null";
   if (isset($_POST["field_OtrosRecursosUsados_creador"])) $creador = $_POST["field_OtrosRecursosUsados_creador"]; else $creador = "null";

 // Eliminando el objeto 
   $col = new OtrosRecursosUsadosCollection($dbconnection);
   $col->removeItem($otro_recurso,$actividad);
?>