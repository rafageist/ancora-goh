<?php 

/** 
  * @name: Receptor of RestriccionDeOtroRecurso identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeOtroRecursoModel"); 
import("model.data.RestriccionDeOtroRecurso"); 
import("model.data.RestriccionDeOtroRecursoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_otro_recurso.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_otro_recurso.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_dia.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_turno.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_valor.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeOtroRecurso_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeOtroRecurso_actividad.php"; 
   if (isset($_POST["field_RestriccionDeOtroRecurso_id"])) $id = $_POST["field_RestriccionDeOtroRecurso_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_otro_recurso"])) $otro_recurso = $_POST["field_RestriccionDeOtroRecurso_otro_recurso"]; else $otro_recurso = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_periodo"])) $periodo = $_POST["field_RestriccionDeOtroRecurso_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_dia"])) $dia = $_POST["field_RestriccionDeOtroRecurso_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_turno"])) $turno = $_POST["field_RestriccionDeOtroRecurso_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_valor"])) $valor = $_POST["field_RestriccionDeOtroRecurso_valor"]; else $valor = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_por_actividad"])) $por_actividad = $_POST["field_RestriccionDeOtroRecurso_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionDeOtroRecurso_actividad"])) $actividad = $_POST["field_RestriccionDeOtroRecurso_actividad"]; else $actividad = null;

 // Eliminando el objeto 
   $col = new RestriccionDeOtroRecursoCollection($dbconnection);
   $col->removeItem($id);
?>