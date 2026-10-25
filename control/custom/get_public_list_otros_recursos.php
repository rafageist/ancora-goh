<?php

	import("model.data.OtroRecursoModel");
	
	$m = new OtroRecursoModel($dbconnection);
	
	// #14: anonymous event. The historical filter is "created by the session
	// user", so an anonymous visitor gets an empty list (the query used to
	// run with an undefined session index). The id comes from the session,
	// not from the request.
	$recursos = array();
	if (isset($_SESSION['user']))
		$recursos = $m->getAllBy("creador = '{$_SESSION['user']}'");
	
	include "../view/custom/public_list_otros_recursos.tpl";

?>