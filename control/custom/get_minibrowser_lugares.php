<?php
	import("model.data.LugarModel");
	import("model.data.Lugar");
	import("model.data.LugarCollection");
	
	$col = new LugarCollection($dbconnection);
	$col->Load(null, null, "*", 'creador = $1 ORDER BY nombre', array($_SESSION['user']));
	
	$lugares = $col->collection;

	include "../view/custom/minibrowser_lugares.tpl";
?>