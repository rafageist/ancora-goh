<?php

import("model.data.ProfesorModel");
import("model.data.Profesor");
import("model.data.ProfesorCollection");

$col = new ProfesorCollection($dbconnection);
$col->Load(null,null,"*","creador = '{$_SESSION['user']}' ORDER BY nombre");

$profesores = $col->collection;

include "../view/custom/minibrowser_profesores.tpl";
?>