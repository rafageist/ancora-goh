<?php 

/** 
  * @name: Receptor of RestriccionDeClasifAct identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeClasifActModel"); 
import("model.data.RestriccionDeClasifAct"); 
import("model.data.RestriccionDeClasifActCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_clasificacion.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php")) include "../control/data/fdms/fdm_RestriccionDeClasifAct_valor.php"; 
   if (isset($_POST["field_RestriccionDeClasifAct_id"])) $id = $_POST["field_RestriccionDeClasifAct_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeClasifAct_clasificacion"])) $clasificacion = $_POST["field_RestriccionDeClasifAct_clasificacion"]; else $clasificacion = null;
   if (isset($_POST["field_RestriccionDeClasifAct_periodo"])) $periodo = $_POST["field_RestriccionDeClasifAct_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeClasifAct_dia"])) $dia = $_POST["field_RestriccionDeClasifAct_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeClasifAct_turno"])) $turno = $_POST["field_RestriccionDeClasifAct_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeClasifAct_valor"])) $valor = $_POST["field_RestriccionDeClasifAct_valor"]; else $valor = null;

 // Eliminando el objeto 
   $col = new RestriccionDeClasifActCollection($dbconnection);
   $col->removeItem($id);
?>