<?php 

/** 
  * @name: Receptor of Receso identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RecesoModel"); 
import("model.data.Receso"); 
import("model.data.RecesoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Receso_periodo.php")) include "../control/data/fdms/fdm_Receso_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Receso_despues_del_turno.php")) include "../control/data/fdms/fdm_Receso_despues_del_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_Receso_duracion.php")) include "../control/data/fdms/fdm_Receso_duracion.php"; 
   if (isset($_POST["field_Receso_periodo"])) $periodo = $_POST["field_Receso_periodo"]; else $periodo = null;
   if (isset($_POST["field_Receso_despues_del_turno"])) $despues_del_turno = $_POST["field_Receso_despues_del_turno"]; else $despues_del_turno = null;
   if (isset($_POST["field_Receso_duracion"])) $duracion = $_POST["field_Receso_duracion"]; else $duracion = null;

 // Eliminando el objeto 
   $col = new RecesoCollection($dbconnection);
   $col->removeItem($periodo,$despues_del_turno);
?>