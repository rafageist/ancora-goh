<?php

import("model.data.EspecialidadModel");
import("model.data.Especialidad");
import("model.data.EspecialidadCollection");

import("model.data.NivelModel");
import("model.data.Nivel");
import("model.data.NivelCollection");

import("model.data.PlanificacionModel");
import("model.data.Planificacion");
import("model.data.PlanificacionCollection");

$coll = new EspecialidadCollection($dbconnection);
$coll->Load(null, null, "creador ='{$_SESSION['user']}'");
$especs = $coll->collection;

$coll = new NivelCollection($dbconnection);
$coll->Load(null, null, "creador ='{$_SESSION['user']}'");
$niveles = $coll->collection;

$coll = new PlanificacionCollection($dbconnection);
$coll->Load(null, null, "creador ='{$_SESSION['user']}'");
$planifs = $coll->collection;

include "../view/custom/browser_organizacion.tpl";

?>