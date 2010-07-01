<?php 

    import("model.data: PlanificacionModel, Planificacion, PlanificacionCollection");
    import("lib.ancora.timeutils.TimeUtils");

    $planifs = new PlanificacionCollection($dbconnection);
    $planifs->Load(null,null,"*","creador ='{$_SESSION['user']}'");

    include "../view/custom/browser_planificaciones.tpl";
   
?>