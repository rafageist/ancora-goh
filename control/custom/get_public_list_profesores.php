<?php

	import("model.data.ProfesorModel");
	
	$m = new ProfesorModel($dbconnection);
	
	$profes = $m->getAll();
	
	include "../view/custom/public_list_profesores.tpl";

?>