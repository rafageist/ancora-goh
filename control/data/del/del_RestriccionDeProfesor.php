<?php 

/** 
  * @name: Receptor of RestriccionDeProfesor identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.RestriccionDeProfesorModel"); 
import("model.data.RestriccionDeProfesor"); 
import("model.data.RestriccionDeProfesorCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_profesor.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_profesor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_periodo.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_dia.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_dia.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_turno.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_turno.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_valor.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_valor.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_por_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_por_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_RestriccionDeProfesor_actividad.php")) include "../control/data/fdms/fdm_RestriccionDeProfesor_actividad.php"; 
   if (isset($_POST["field_RestriccionDeProfesor_id"])) $id = $_POST["field_RestriccionDeProfesor_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeProfesor_profesor"])) $profesor = $_POST["field_RestriccionDeProfesor_profesor"]; else $profesor = null;
   if (isset($_POST["field_RestriccionDeProfesor_periodo"])) $periodo = $_POST["field_RestriccionDeProfesor_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeProfesor_dia"])) $dia = $_POST["field_RestriccionDeProfesor_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeProfesor_turno"])) $turno = $_POST["field_RestriccionDeProfesor_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeProfesor_valor"])) $valor = $_POST["field_RestriccionDeProfesor_valor"]; else $valor = null;
   if (isset($_POST["field_RestriccionDeProfesor_por_actividad"])) $por_actividad = $_POST["field_RestriccionDeProfesor_por_actividad"]; else $por_actividad = null;
   if (isset($_POST["field_RestriccionDeProfesor_actividad"])) $actividad = $_POST["field_RestriccionDeProfesor_actividad"]; else $actividad = null;

 // Eliminando el objeto 
   $col = new RestriccionDeProfesorCollection($dbconnection);
   $col->removeItem($id);
?>