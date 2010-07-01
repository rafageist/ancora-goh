<?php 

/** 
  * @name: Receptor of Actividad data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.ActividadModel"); 
import("model.data.Actividad"); 

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
   $plantilla = $_POST["field_Actividad_plantilla"];
   $grupo = $_POST["field_Actividad_grupo"];
   $profesor = $_POST["field_Actividad_profesor"];
   $lugar = $_POST["field_Actividad_lugar"];
   $dia = $_POST["field_Actividad_dia"];
   $turno = $_POST["field_Actividad_turno"];
   $momento_de_colocacion = $_POST["field_Actividad_momento_de_colocacion"];
   $creador = $_POST["field_Actividad_creador"];

 // Construyendo el objeto 
   $obj = new Actividad($plantilla,$grupo,$profesor,$lugar,$dia,$turno,$momento_de_colocacion,$creador,"",true, null, false);
?>