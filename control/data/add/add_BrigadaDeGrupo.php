<?php 

/** 
  * @name: Receptor of BrigadaDeGrupo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.BrigadaDeGrupoModel"); 
import("model.data.BrigadaDeGrupo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php"; 

// Getting data from POST 
   $brigada = $_POST["field_BrigadaDeGrupo_brigada"];
   $grupo = $_POST["field_BrigadaDeGrupo_grupo"];

 // Construyendo el objeto 
   $obj = new BrigadaDeGrupo($brigada,$grupo,true, null, false);
?>