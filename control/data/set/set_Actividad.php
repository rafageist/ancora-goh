<?php 

/** 
  * @name: Receptor of Actividad data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.ActividadModel"); 
import("model.data.Actividad"); 
import("model.data.ActividadCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_Actividad_plantilla.php")) include "../control/data/fdms/fdm_Actividad_plantilla.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_grupo.php")) include "../control/data/fdms/fdm_Actividad_grupo.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_profesor.php")) include "../control/data/fdms/fdm_Actividad_profesor.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_lugar.php")) include "../control/data/fdms/fdm_Actividad_lugar.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_dia.php")) include "../control/data/fdms/fdm_Actividad_dia.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_turno.php")) include "../control/data/fdms/fdm_Actividad_turno.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_momento_de_colocacion.php")) include "../control/data/fdms/fdm_Actividad_momento_de_colocacion.php"; 
if (file_exists("../control/data/fdms/fdm_Actividad_creador.php")) include "../control/data/fdms/fdm_Actividad_creador.php"; 

// Getting data from POST 
   if (isset($_POST["field_Actividad_id"])) $id = $_POST["field_Actividad_id"]; else $id = "null";
   if (isset($_POST["field_Actividad_plantilla"])) $plantilla = $_POST["field_Actividad_plantilla"]; else $plantilla = "null";
   if (isset($_POST["field_Actividad_grupo"])) $grupo = $_POST["field_Actividad_grupo"]; else $grupo = "null";
   if (isset($_POST["field_Actividad_profesor"])) $profesor = $_POST["field_Actividad_profesor"]; else $profesor = "null";
   if (isset($_POST["field_Actividad_lugar"])) $lugar = $_POST["field_Actividad_lugar"]; else $lugar = "null";
   if (isset($_POST["field_Actividad_dia"])) $dia = $_POST["field_Actividad_dia"]; else $dia = "null";
   if (isset($_POST["field_Actividad_turno"])) $turno = $_POST["field_Actividad_turno"]; else $turno = "null";
   if (isset($_POST["field_Actividad_momento_de_colocacion"])) $momento_de_colocacion = $_POST["field_Actividad_momento_de_colocacion"]; else $momento_de_colocacion = "null";
   if (isset($_POST["field_Actividad_creador"])) $creador = $_POST["field_Actividad_creador"]; else $creador = "null";

 // Actualizando el objeto 
   $col = new ActividadCollection($dbconnection);
   $obj = $col->getItem($id);
$obj->setPlantilla($plantilla);
$obj->setGrupo($grupo);
$obj->setProfesor($profesor);
$obj->setLugar($lugar);
$obj->setDia($dia);
$obj->setTurno($turno);
$obj->setMomentoDeColocacion($momento_de_colocacion);
$obj->setCreador($creador);
?>