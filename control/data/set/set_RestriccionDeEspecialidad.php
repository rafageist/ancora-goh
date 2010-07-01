<?php 

/** 
  * @name: Receptor of RestriccionDeEspecialidad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.RestriccionDeEspecialidadModel"); 
import("model.data.RestriccionDeEspecialidad"); 
import("model.data.RestriccionDeEspecialidadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_especialidad.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_especialidad.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_periodo.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_periodo.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_dia.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_dia.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_turno.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_turno.php"; 
if (file_exists("../control/data/fdms/fdm_RestriccionDeEspecialidad_valor.php")) include "../control/data/fdms/fdm_RestriccionDeEspecialidad_valor.php"; 

// Getting data from POST 
   if (isset($_POST["field_RestriccionDeEspecialidad_id"])) $id = $_POST["field_RestriccionDeEspecialidad_id"]; else $id = "null";
   if (isset($_POST["field_RestriccionDeEspecialidad_especialidad"])) $especialidad = $_POST["field_RestriccionDeEspecialidad_especialidad"]; else $especialidad = "null";
   if (isset($_POST["field_RestriccionDeEspecialidad_periodo"])) $periodo = $_POST["field_RestriccionDeEspecialidad_periodo"]; else $periodo = "null";
   if (isset($_POST["field_RestriccionDeEspecialidad_dia"])) $dia = $_POST["field_RestriccionDeEspecialidad_dia"]; else $dia = "null";
   if (isset($_POST["field_RestriccionDeEspecialidad_turno"])) $turno = $_POST["field_RestriccionDeEspecialidad_turno"]; else $turno = "null";
   if (isset($_POST["field_RestriccionDeEspecialidad_valor"])) $valor = $_POST["field_RestriccionDeEspecialidad_valor"]; else $valor = "null";

 // Actualizando el objeto 
   $col = new RestriccionDeEspecialidadCollection($dbconnection);
   $obj = $col->getItem($id);
$obj->setEspecialidad($especialidad);
$obj->setPeriodo($periodo);
$obj->setDia($dia);
$obj->setTurno($turno);
$obj->setValor($valor);
?>