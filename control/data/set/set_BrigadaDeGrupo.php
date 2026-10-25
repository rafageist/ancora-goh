<?php 

/** 
  * @name: Receptor of BrigadaDeGrupo data from POST for UPDATE 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 

import("model.data.BrigadaDeGrupoModel"); 
import("model.data.BrigadaDeGrupo"); 
import("model.data.BrigadaDeGrupoCollection"); 

// Execute fields's modification data 
if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_brigada.php"; 
if (file_exists("../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php")) include "../control/data/fdms/fdm_BrigadaDeGrupo_grupo.php"; 

// Getting data from POST 
   if (isset($_POST["field_BrigadaDeGrupo_brigada"])) $brigada = $_POST["field_BrigadaDeGrupo_brigada"]; else $brigada = null;
   if (isset($_POST["field_BrigadaDeGrupo_grupo"])) $grupo = $_POST["field_BrigadaDeGrupo_grupo"]; else $grupo = null;

 // Actualizando el objeto 
   $col = new BrigadaDeGrupoCollection($dbconnection);
   $obj = $col->getItem($brigada,$grupo);
if ($brigada !== null) $obj->setBrigada($brigada);
if ($grupo !== null) $obj->setGrupo($grupo);
?>