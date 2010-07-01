<?php

import("model.data.EspecialidadModel");
import("model.data.Especialidad");
import("model.data.EspecialidadCollection");

$id = $_POST['id'];
$for_new  = false; 

$coll = new EspecialidadCollection($dbconnection);
$esp = $coll->getItem($id);

$descripcion = $esp->descripcion;

include "../view/custom/form_especialidad.tpl";
?>