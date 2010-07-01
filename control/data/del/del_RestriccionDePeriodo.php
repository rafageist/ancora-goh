<?php 

/** 
  * @name: Receptor of RestriccionDePeriodo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDePeriodoModel"); 
import("model.data.RestriccionDePeriodo"); 
import("model.data.RestriccionDePeriodoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_dia.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_turno.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDePeriodo_valor.php")) include "../control/data/fdms/fdm_RestriccionDePeriodo_valor.php"; 
   if (isset($_POST["field_RestriccionDePeriodo_id"])) $id = $_POST["field_RestriccionDePeriodo_id"]; else $id = "null";
   if (isset($_POST["field_RestriccionDePeriodo_periodo"])) $periodo = $_POST["field_RestriccionDePeriodo_periodo"]; else $periodo = "null";
   if (isset($_POST["field_RestriccionDePeriodo_dia"])) $dia = $_POST["field_RestriccionDePeriodo_dia"]; else $dia = "null";
   if (isset($_POST["field_RestriccionDePeriodo_turno"])) $turno = $_POST["field_RestriccionDePeriodo_turno"]; else $turno = "null";
   if (isset($_POST["field_RestriccionDePeriodo_valor"])) $valor = $_POST["field_RestriccionDePeriodo_valor"]; else $valor = "null";

 // Eliminando el objeto 
   $col = new RestriccionDePeriodoCollection($dbconnection);
   $col->removeItem($id);
?>