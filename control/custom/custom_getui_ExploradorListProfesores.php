<?php

	import("model.data.ProfesorModel");
	
	$m = new ProfesorModel($dbconnection);
	
	$profes = $m->getAll();
	
	include "../view/custom/explorador_list_profesores.tpl";

?>