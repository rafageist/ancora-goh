<?php 

/** 
  * @name: Receptor of BrigadaDeGrupo identification from POST for DELETE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.BrigadaDeGrupoModel"); 
import("model.data.BrigadaDeGrupo"); 
import("model.data.BrigadaDeGrupoCollection"); 

// Tomando los valores del POST 
   if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php"; 
   if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php"; 
   if (isset($_POST["field_BrigadaDeGrupo_brigada"])) $brigada = $_POST["field_BrigadaDeGrupo_brigada"]; else $brigada = "null";
   if (isset($_POST["field_BrigadaDeGrupo_grupo"])) $grupo = $_POST["field_BrigadaDeGrupo_grupo"]; else $grupo = "null";

 // Eliminando el objeto 
   $col = new BrigadaDeGrupoCollection($dbconnection);
   $col->removeItem($brigada,$grupo);
?>