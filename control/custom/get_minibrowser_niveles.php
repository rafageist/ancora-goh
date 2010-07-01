<?php

import("model.data.NivelModel");
import("model.data.Nivel");
import("model.data.NivelCollection");

$coll = new NivelCollection($dbconnection);
$coll->Load(null, null, "creador ='{$_SESSION['user']}'");
$niveles = $coll->collection;

include "../view/custom/browser_organizacion_left.tpl";


?>