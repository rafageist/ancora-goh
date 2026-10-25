<?php 

/** 
  * @name: Receptor of Desglose identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.DesgloseModel"); 
import("model.data.Desglose"); 
import("model.data.DesgloseCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_Desglose_asignatura.php")) include "../control/data/fdms/fdm_Desglose_asignatura.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_periodo.php")) include "../control/data/fdms/fdm_Desglose_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_min_dias.php")) include "../control/data/fdms/fdm_Desglose_min_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_max_dias.php")) include "../control/data/fdms/fdm_Desglose_max_dias.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_respetar_orden.php")) include "../control/data/fdms/fdm_Desglose_respetar_orden.php"; 
   if (file_exists("../control/data/fdms/fdm_Desglose_mismo_dia.php")) include "../control/data/fdms/fdm_Desglose_mismo_dia.php"; 
   if (isset($_POST["field_Desglose_id"])) $id = $_POST["field_Desglose_id"]; else $id = null;
   if (isset($_POST["field_Desglose_asignatura"])) $asignatura = $_POST["field_Desglose_asignatura"]; else $asignatura = null;
   if (isset($_POST["field_Desglose_periodo"])) $periodo = $_POST["field_Desglose_periodo"]; else $periodo = null;
   if (isset($_POST["field_Desglose_min_dias"])) $min_dias = $_POST["field_Desglose_min_dias"]; else $min_dias = null;
   if (isset($_POST["field_Desglose_max_dias"])) $max_dias = $_POST["field_Desglose_max_dias"]; else $max_dias = null;
   if (isset($_POST["field_Desglose_respetar_orden"])) $respetar_orden = $_POST["field_Desglose_respetar_orden"]; else $respetar_orden = null;
   if (isset($_POST["field_Desglose_mismo_dia"])) $mismo_dia = $_POST["field_Desglose_mismo_dia"]; else $mismo_dia = null;

 // Eliminando el objeto 
   $col = new DesgloseCollection($dbconnection);
   $col->removeItem($id);
?>