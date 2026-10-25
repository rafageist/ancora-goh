<?php 

/** 
  * @name: Receptor of Periodo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 

import("model.data.PeriodoModel"); 
import("model.data.Periodo"); 
import("model.data.PeriodoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Periodo_descripcion.php")) include "../control/data/fdms/fdm_Periodo_descripcion.php"; 
if (file_exists("../control/data/fdms/fdm_Periodo_cant_dias.php")) include "../control/data/fdms/fdm_Periodo_cant_dias.php"; 
if (file_exists("../control/data/fdms/fdm_Periodo_cant_turnos.php")) include "../control/data/fdms/fdm_Periodo_cant_turnos.php"; 
if (file_exists("../control/data/fdms/fdm_Periodo_hora_inicial.php")) include "../control/data/fdms/fdm_Periodo_hora_inicial.php"; 
if (file_exists("../control/data/fdms/fdm_Periodo_duracion_del_turno.php")) include "../control/data/fdms/fdm_Periodo_duracion_del_turno.php"; 
if (file_exists("../control/data/fdms/fdm_Periodo_planificacion.php")) include "../control/data/fdms/fdm_Periodo_planificacion.php"; 

// Getting data from POST 
   if (isset($_POST["field_Periodo_id"])) $id = $_POST["field_Periodo_id"]; else $id = null;
   if (isset($_POST["field_Periodo_descripcion"])) $descripcion = $_POST["field_Periodo_descripcion"]; else $descripcion = null;
   if (isset($_POST["field_Periodo_cant_dias"])) $cant_dias = $_POST["field_Periodo_cant_dias"]; else $cant_dias = null;
   if (isset($_POST["field_Periodo_cant_turnos"])) $cant_turnos = $_POST["field_Periodo_cant_turnos"]; else $cant_turnos = null;
   if (isset($_POST["field_Periodo_hora_inicial"])) $hora_inicial = $_POST["field_Periodo_hora_inicial"]; else $hora_inicial = null;
   if (isset($_POST["field_Periodo_duracion_del_turno"])) $duracion_del_turno = $_POST["field_Periodo_duracion_del_turno"]; else $duracion_del_turno = null;
   if (isset($_POST["field_Periodo_planificacion"])) $planificacion = $_POST["field_Periodo_planificacion"]; else $planificacion = null;

 // Actualizando el objeto 
   $col = new PeriodoCollection($dbconnection);
   $obj = $col->getItem($id);
if ($descripcion !== null) $obj->setDescripcion($descripcion);
if ($cant_dias !== null) $obj->setCantDias($cant_dias);
if ($cant_turnos !== null) $obj->setCantTurnos($cant_turnos);
if ($hora_inicial !== null) $obj->setHoraInicial($hora_inicial);
if ($duracion_del_turno !== null) $obj->setDuracionDelTurno($duracion_del_turno);
if ($planificacion !== null) $obj->setPlanificacion($planificacion);
?>