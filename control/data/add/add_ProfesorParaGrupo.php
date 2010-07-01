<?php 

/** 
  * @name: Receptor of ProfesorParaGrupo data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorParaGrupoModel"); 
import("model.data.ProfesorParaGrupo"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaGrupo_profesor_para_actividad.php")) include "../control/data/fdms/fdm_ProfesorParaGrupo_profesor_para_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaGrupo_grupo.php")) include "../control/data/fdms/fdm_ProfesorParaGrupo_grupo.php"; 

// Getting data from POST 
   $profesor_para_actividad = $_POST["field_ProfesorParaGrupo_profesor_para_actividad"];
   $grupo = $_POST["field_ProfesorParaGrupo_grupo"];

 // Construyendo el objeto 
   $obj = new ProfesorParaGrupo($profesor_para_actividad,$grupo,true, null, false);
?>