<?php

import("model.data.EspecialidadModel");
import("model.data.Especialidad");
import("model.data.EspecialidadCollection");

$coll = new EspecialidadCollection($dbconnection);
$coll->Load(null, null, "creador ='{$_SESSION['user']}'");
$especs = $coll->collection;

include "../view/custom/browser_organizacion_top.tpl";


?>