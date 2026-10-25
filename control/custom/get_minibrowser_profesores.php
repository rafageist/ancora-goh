<?php

import("model.data.ProfesorModel");
import("model.data.Profesor");
import("model.data.ProfesorCollection");

$col = new ProfesorCollection($dbconnection);
$col->Load(null, null, "*", 'creador = $1 ORDER BY nombre', array($_SESSION['user']));

$profesores = $col->collection;

include "../view/custom/minibrowser_profesores.tpl";
?>