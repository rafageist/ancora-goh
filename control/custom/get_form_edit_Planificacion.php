<?php

	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");	
	import("model.data.PlanificacionCollection");
	import("lib.ancora.timeutils.TimeUtils");

   $id = $_POST['id'];
   
   $ps = new PlanificacionCollection($dbconnection);
   
   $item = $ps->getItem($id);
   
   $descripcion = $item->descripcion;
   
   $d = TimeUtils::number_to_date($item->fecha_ini);
   
   $fecha_ini = $d->d."/".$d->m."/".$d->a;
   
   $d = TimeUtils::number_to_date($item->fecha_ini + $item->cant_dias);
   
   $fecha_fin = $d->d."/".$d->m."/".$d->a;
   
   include "../view/custom/form_planificacion.tpl";
   
 ?>