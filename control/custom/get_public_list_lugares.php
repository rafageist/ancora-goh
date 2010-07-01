<?php

	import("model.data.LugarModel");
	
	$m = new LugarModel($dbconnection);
	
	$lugares = $m->getAll();
	
	include "../view/custom/public_list_lugares.tpl";

?>