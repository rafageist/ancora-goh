<?php
$for_new = false;

import ( "lib.ancora.timeutils.TimeUtils" );

import ( "model.data.PeriodoModel" );
import ( "model.data.Periodo" );
import ( "model.data.PeriodoCollection" );

import ( "model.data.PlanificacionModel" );
import ( "model.data.Planificacion" );
import ( "model.data.PlanificacionCollection" );

import ( "model.data.PeriodicidadModel" );
import ( "model.data.Periodicidad" );
import ( "model.data.PeriodicidadCollection" );

$id = $_POST ['id'];

$periodos = new PeriodoCollection ( $dbconnection );
$periodo = $periodos->getItem ( $id );


$planificaciones = new PlanificacionCollection ( $dbconnection );
$planificaciones->Load ( null, null, "*", "creador = '{$_SESSION['user']}'" );

$planif = $planificaciones->getItem ( $periodo->planificacion );


$idplanif = $planif->id;
$cant_dias_planificacion = $planif->cant_dias;
$descripcion = $periodo->descripcion;
$cant_dias = $periodo->cant_dias;
$cant_turnos = $periodo->cant_turnos;
$hora_inicial = $periodo->hora_inicial;
$duracion_del_turno = $periodo->duracion_del_turno;

$col = new PeriodicidadCollection ( $dbconnection );

$col->Load ( null, null, "*", "periodo = '$id'" );

$periodicidades = array ();

if (is_array($col->collection))
foreach ( $col->collection as $item ) {
	$periodicidades [$item->dia] = true;
}

include "../view/custom/form_periodo_periodicidad.tpl";
?>