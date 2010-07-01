<?php
	import("model.data.LugarModel");
	import("model.data.Lugar");
	import("model.data.LugarCollection");
	
	$col = new LugarCollection($dbconnection);
	$col->Load(null, null,"*","creador = '{$_SESSION['user']}' ORDER BY nombre");
	
	$lugares = $col->collection;

	include "../view/custom/minibrowser_lugares.tpl";
?>