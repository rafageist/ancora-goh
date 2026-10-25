<?php
	import("model.data.ClasificacionDeActividadModel");
	import("model.data.ClasificacionDeActividad");
	import("model.data.ClasificacionDeActividadCollection");
	
	$col = new ClasificacionDeActividadCollection($dbconnection);
	$col->Load(null, null, "*", 'creador = $1 ORDER BY prioridad', array($_SESSION['user']));
	$clasifs = $col->collection;
	include "../view/custom/minibrowser_clasifact.tpl";
?>