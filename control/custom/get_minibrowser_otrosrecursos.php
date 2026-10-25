<?php

import("model.data.OtroRecursoModel");
import("model.data.OtroRecurso");
import("model.data.OtroRecursoCollection");

$col = new OtroRecursoCollection($dbconnection);
$col->Load(null, null, "*", 'creador = $1 ORDER BY nombre', array($_SESSION['user']));

$recursos = $col->collection;

include "../view/custom/minibrowser_otrosrecursos.tpl";
?>