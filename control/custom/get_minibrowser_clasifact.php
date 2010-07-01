<?php
	import("model.data.ClasificacionDeActividadModel");
	import("model.data.ClasificacionDeActividad");
	import("model.data.ClasificacionDeActividadCollection");
	
	$col = new ClasificacionDeActividadCollection($dbconnection);
	$col->Load(null,null,"*","creador = '{$_SESSION['user']}' ORDER BY prioridad");
	$clasifs = $col->collection;
	include "../view/custom/minibrowser_clasifact.tpl";
?>