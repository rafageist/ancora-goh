<?php 

/** 
  * @name: Receptor of ProfesorParaGrupo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:14 
  * @version: 1.0 
  */ 
import("model.data.ProfesorParaGrupoModel"); 
import("model.data.ProfesorParaGrupo"); 
import("model.data.ProfesorParaGrupoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaGrupo_profesor_para_actividad.php")) include "../control/data/fdms/fdm_ProfesorParaGrupo_profesor_para_actividad.php"; 
   if (file_exists("../control/data/fdms/fdm_ProfesorParaGrupo_grupo.php")) include "../control/data/fdms/fdm_ProfesorParaGrupo_grupo.php"; 
   if (isset($_POST["field_ProfesorParaGrupo_profesor_para_actividad"])) $profesor_para_actividad = $_POST["field_ProfesorParaGrupo_profesor_para_actividad"]; else $profesor_para_actividad = null;
   if (isset($_POST["field_ProfesorParaGrupo_grupo"])) $grupo = $_POST["field_ProfesorParaGrupo_grupo"]; else $grupo = null;

 // Eliminando el objeto 
   $col = new ProfesorParaGrupoCollection($dbconnection);
   $col->removeItem($profesor_para_actividad,$grupo);
?>