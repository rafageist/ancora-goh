<?php

	import("model.data.LugarModel");
	
	$m = new LugarModel($dbconnection);
	
	$lugares = $m->getAll();
	
	include "../view/custom/explorador_list_lugares.tpl";

?>