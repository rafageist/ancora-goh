<?php
$for_new = false;

import ( "lib.ancora.timeutils.TimeUtils" );

import ( "model.data.PeriodoModel" );
import ( "model.data.Periodo" );
import ( "model.data.PeriodoCollection" );

import ( "model.data.PlanificacionModel" );
import ( "model.data.Planificacion" );
import ( "model.data.PlanificacionCollection" );

import ( "model.data.RestriccionDePeriodoModel" );
import ( "model.data.RestriccionDePeriodo" );
import ( "model.data.RestriccionDePeriodoCollection" );

$id = $_POST ['id'];

$periodos = new PeriodoCollection ( $dbconnection );
$periodo = $periodos->getItem ( $id );

$planificaciones = new PlanificacionCollection ( $dbconnection );
$planificaciones->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));

$planif = $planificaciones->getItem ( $periodo->planificacion );

$idplanif = $planif->id;
$cant_dias_planificacion = $planif->cant_dias;
$descripcion = $periodo->descripcion;
$cant_dias = $periodo->cant_dias;
$cant_turnos = $periodo->cant_turnos;
$hora_inicial = $periodo->hora_inicial;
$duracion_del_turno = $periodo->duracion_del_turno;

$restricciones = $periodo->getRestriccionDePeriodo ();

$rest = array ();
if (is_array ( $restricciones->collection ))
	foreach ( $restricciones->collection as $r ) {
		if (! isset ( $rest [$r->dia] ))
			$rest [$r->dia] = array ();
		$rest [$r->dia] [$r->turno] = $r->valor;
	}

include "../view/custom/form_periodo_restricciones.tpl";
?>