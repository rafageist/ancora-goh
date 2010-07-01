<?php 

/** 
  * @name: Receptor of RestriccionDeBrigada identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeBrigadaModel"); 
import("model.data.RestriccionDeBrigada"); 
import("model.data.RestriccionDeBrigadaCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_brigada.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_dia.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_turno.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_valor.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeBrigada_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeBrigada_actividad.php"; 
   if (isset($_POST["field_RestriccionDeBrigada_id"])) $id = $_POST["field_RestriccionDeBrigada_id"]; else $id = "null";
   if (isset($_POST["field_RestriccionDeBrigada_brigada"])) $brigada = $_POST["field_RestriccionDeBrigada_brigada"]; else $brigada = "null";
   if (isset($_POST["field_RestriccionDeBrigada_periodo"])) $periodo = $_POST["field_RestriccionDeBrigada_periodo"]; else $periodo = "null";
   if (isset($_POST["field_RestriccionDeBrigada_dia"])) $dia = $_POST["field_RestriccionDeBrigada_dia"]; else $dia = "null";
   if (isset($_POST["field_RestriccionDeBrigada_turno"])) $turno = $_POST["field_RestriccionDeBrigada_turno"]; else $turno = "null";
   if (isset($_POST["field_RestriccionDeBrigada_valor"])) $valor = $_POST["field_RestriccionDeBrigada_valor"]; else $valor = "null";
   if (isset($_POST["field_RestriccionDeBrigada_por_actividad"])) $por_actividad = $_POST["field_RestriccionDeBrigada_por_actividad"]; else $por_actividad = "null";
   if (isset($_POST["field_RestriccionDeBrigada_actividad"])) $actividad = $_POST["field_RestriccionDeBrigada_actividad"]; else $actividad = "null";

 // Eliminando el objeto 
   $col = new RestriccionDeBrigadaCollection($dbconnection);
   $col->removeItem($id);
?>