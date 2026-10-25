<?php

   import("model.data.BrigadaModel");
   import("model.data.Brigada");
   import("model.data.BrigadaCollection");
   import("model.data.EspecialidadModel");
   import("model.data.Especialidad");
   import("model.data.EspecialidadCollection");
   import("model.data.NivelModel");
   import("model.data.Nivel");
   import("model.data.NivelCollection");
   import("model.data.PlanificacionModel");
   
   $especs =  new EspecialidadModel($dbconnection);   
   $niveles =  new NivelModel($dbconnection);   
   $m = new PlanificacionModel($dbconnection);
   $planificaciones = $m->getAllBy('creador = $1', "*", false, array($_SESSION['user']));

   include "../view/custom/browser_brigadas.tpl";

?>