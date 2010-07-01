<?php

	import("model.data.PublicFunctions");
	
	$m = new PublicFunctions($dbconnection);
	
	$id = $_POST['id'];
	
	$m->BajaPrioridadClasif($id);

?>