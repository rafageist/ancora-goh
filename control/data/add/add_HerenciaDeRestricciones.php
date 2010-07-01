<?php 

/** 
  * @name: Receptor of HerenciaDeRestricciones data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.HerenciaDeRestriccionesModel"); 
import("model.data.HerenciaDeRestricciones"); 

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
   $planificacion = $_POST["field_HerenciaDeRestricciones_planificacion"];
   $activa = $_POST["field_HerenciaDeRestricciones_activa"];
   $hijo_brigada = $_POST["field_HerenciaDeRestricciones_hijo_brigada"];
   $hijo_profesor = $_POST["field_HerenciaDeRestricciones_hijo_profesor"];
   $hijo_lugar = $_POST["field_HerenciaDeRestricciones_hijo_lugar"];
   $hijo_otro_recurso = $_POST["field_HerenciaDeRestricciones_hijo_otro_recurso"];
   $padre_brigada = $_POST["field_HerenciaDeRestricciones_padre_brigada"];
   $padre_profesor = $_POST["field_HerenciaDeRestricciones_padre_profesor"];
   $padre_lugar = $_POST["field_HerenciaDeRestricciones_padre_lugar"];
   $padre_otro_recurso = $_POST["field_HerenciaDeRestricciones_padre_otro_recurso"];

 // Construyendo el objeto 
   $obj = new HerenciaDeRestricciones($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,"",true, null, false);
?>