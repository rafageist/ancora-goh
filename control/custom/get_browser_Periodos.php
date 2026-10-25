<?php

   import("model.data.PeriodoModel");
   import("model.data.Periodo");
   import("model.data.PeriodoCollection");
   
   import("model.data.PlanificacionModel");
   import("model.data.Planificacion");
   import("model.data.PlanificacionCollection");
   
   $planificacion = $_POST['planificacion'];  
   
   $coll = new PlanificacionCollection($dbconnection);
   $planif = $coll->getItem($planificacion);
   $planifdesc = $planif->descripcion;

   $periodos = new PeriodoCollection($dbconnection);
   $periodos->Load(null, null, "*", 'dame_creador_de_planificacion(planificacion) = $1 AND planificacion = $2', array($_SESSION['user'], $planificacion));
   
   include "../view/custom/browser_periodos.tpl";

?>