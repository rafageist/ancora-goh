<?php

	$for_new = true;
	
	if (isset($_POST['especialidad'])) $especialidad = $_POST['especialidad'];
	if (isset($_POST['nivel'])) $nivel = $_POST['nivel'];
	
	include "../view/custom/form_asignatura.tpl";
?>