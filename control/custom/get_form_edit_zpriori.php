<?php

import ( "model.data.ClasificacionDeActividadModel" );
import ( "model.data.ClasificacionDeActividad" );
import ( "model.data.ClasificacionDeActividadCollection" );

import ( "model.data.ZprioriModel" );
import ( "model.data.Zpriori" );
import ( "model.data.ZprioriCollection" );

import ( "model.data.PlanificacionModel" );
import ( "model.data.Planificacion" );
import ( "model.data.PlanificacionCollection" );

import ( "model.data.PeriodoModel" );
import ( "model.data.Periodo" );
import ( "model.data.PeriodoCollection" );

$planifs = array ();

$col = new PlanificacionCollection ( $dbconnection );

$col->Load ( null, null, "creador = '{$_SESSION['user']}'" );

if (is_array ( $col->collection )) {
	
	$i = 0;
	foreach ( $col->collection as $item ) {
		$planifs [$i] = $item;
		$planifs [$i]->periodos = $item->getPeriodo ();
		$i ++;
	}
}

$idclasif = $_POST ['clasif'];

$zpriori = array ();

$idperiodo = '';

$periodo = false;


if (isset ( $_POST ['periodo'] )) {
	$idperiodo = $_POST ['periodo'];
	
	$col = new ZprioriCollection ( $dbconnection );
	
	$col->Load ( null, null, "*", "clasificacion = '$idclasif' AND periodo = '$idperiodo'" );
	
	$zps = $col->collection;
	
	$col = new PeriodoCollection ( $dbconnection );
	
	$periodo = $col->getItem ( $idperiodo );
	
	for($i = 1; $i <= $periodo->cant_dias; $i ++)
		for($j = 1; $j <= $periodo->cant_turnos; $j ++)
			$zpriori [$i] [$j] = 1;
}

if (is_array ( $zps ))
	foreach ( $zps as $zp )
		$zpriori [$zp->dia] [$zp->turno] = $zp->valor;

include "../view/custom/form_zpriori.tpl";
?>