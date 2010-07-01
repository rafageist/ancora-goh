<?php 

/** 
  * @name: Receptor of HerenciaDeRestricciones data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.HerenciaDeRestriccionesModel"); 
import("model.data.HerenciaDeRestricciones"); 
import("model.data.HerenciaDeRestriccionesCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_planificacion.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_planificacion.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_activa.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_activa.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_brigada.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_brigada.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_profesor.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_profesor.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_lugar.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_lugar.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_otro_recurso.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_hijo_otro_recurso.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_padre_brigada.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_padre_brigada.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_padre_profesor.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_padre_profesor.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_padre_lugar.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_padre_lugar.php"; 
if (file_exists("../control/data/fdms/fdm_HerenciaDeRestricciones_padre_otro_recurso.php")) include "../control/data/fdms/fdm_HerenciaDeRestricciones_padre_otro_recurso.php"; 

// Getting data from POST 
   if (isset($_POST["field_HerenciaDeRestricciones_id"])) $id = $_POST["field_HerenciaDeRestricciones_id"]; else $id = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_planificacion"])) $planificacion = $_POST["field_HerenciaDeRestricciones_planificacion"]; else $planificacion = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_activa"])) $activa = $_POST["field_HerenciaDeRestricciones_activa"]; else $activa = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_hijo_brigada"])) $hijo_brigada = $_POST["field_HerenciaDeRestricciones_hijo_brigada"]; else $hijo_brigada = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_hijo_profesor"])) $hijo_profesor = $_POST["field_HerenciaDeRestricciones_hijo_profesor"]; else $hijo_profesor = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_hijo_lugar"])) $hijo_lugar = $_POST["field_HerenciaDeRestricciones_hijo_lugar"]; else $hijo_lugar = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_hijo_otro_recurso"])) $hijo_otro_recurso = $_POST["field_HerenciaDeRestricciones_hijo_otro_recurso"]; else $hijo_otro_recurso = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_padre_brigada"])) $padre_brigada = $_POST["field_HerenciaDeRestricciones_padre_brigada"]; else $padre_brigada = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_padre_profesor"])) $padre_profesor = $_POST["field_HerenciaDeRestricciones_padre_profesor"]; else $padre_profesor = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_padre_lugar"])) $padre_lugar = $_POST["field_HerenciaDeRestricciones_padre_lugar"]; else $padre_lugar = "null";
   if (isset($_POST["field_HerenciaDeRestricciones_padre_otro_recurso"])) $padre_otro_recurso = $_POST["field_HerenciaDeRestricciones_padre_otro_recurso"]; else $padre_otro_recurso = "null";

 // Actualizando el objeto 
   $col = new HerenciaDeRestriccionesCollection($dbconnection);
   $obj = $col->getItem($id);
$obj->setPlanificacion($planificacion);
$obj->setActiva($activa);
$obj->setHijoBrigada($hijo_brigada);
$obj->setHijoProfesor($hijo_profesor);
$obj->setHijoLugar($hijo_lugar);
$obj->setHijoOtroRecurso($hijo_otro_recurso);
$obj->setPadreBrigada($padre_brigada);
$obj->setPadreProfesor($padre_profesor);
$obj->setPadreLugar($padre_lugar);
$obj->setPadreOtroRecurso($padre_otro_recurso);
?>