<?php 

/** 
  * @name: Receptor of RestriccionDeAsignatura data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionDeAsignaturaModel"); 
import("model.data.RestriccionDeAsignatura"); 
import("model.data.RestriccionDeAsignaturaCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_asignatura.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_asignatura.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_dia.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_dia.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_turno.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_turno.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeAsignatura_valor.php")) include "../control/data/fdms/fdm_RestriccionDeAsignatura_valor.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionDeAsignatura_id"])) $id = $_POST["field_RestriccionDeAsignatura_id"]; else $id = null;
   if (isset($_POST["field_RestriccionDeAsignatura_asignatura"])) $asignatura = $_POST["field_RestriccionDeAsignatura_asignatura"]; else $asignatura = null;
   if (isset($_POST["field_RestriccionDeAsignatura_periodo"])) $periodo = $_POST["field_RestriccionDeAsignatura_periodo"]; else $periodo = null;
   if (isset($_POST["field_RestriccionDeAsignatura_dia"])) $dia = $_POST["field_RestriccionDeAsignatura_dia"]; else $dia = null;
   if (isset($_POST["field_RestriccionDeAsignatura_turno"])) $turno = $_POST["field_RestriccionDeAsignatura_turno"]; else $turno = null;
   if (isset($_POST["field_RestriccionDeAsignatura_valor"])) $valor = $_POST["field_RestriccionDeAsignatura_valor"]; else $valor = null;

 // Actualizando el objeto 
   $col = new RestriccionDeAsignaturaCollection($dbconnection);
   $obj = $col->getItem($id);
if ($asignatura !== null) $obj->setAsignatura($asignatura);
if ($periodo !== null) $obj->setPeriodo($periodo);
if ($dia !== null) $obj->setDia($dia);
if ($turno !== null) $obj->setTurno($turno);
if ($valor !== null) $obj->setValor($valor);
?>