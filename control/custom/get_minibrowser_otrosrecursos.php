<?php

import("model.data.OtroRecursoModel");
import("model.data.OtroRecurso");
import("model.data.OtroRecursoCollection");

$col = new OtroRecursoCollection($dbconnection);
$col->Load(null,null,"*","creador = '{$_SESSION['user']}' ORDER BY nombre");

$recursos = $col->collection;

include "../view/custom/minibrowser_otrosrecursos.tpl";
?>