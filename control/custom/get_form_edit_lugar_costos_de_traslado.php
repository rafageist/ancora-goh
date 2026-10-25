<?php

import ( "model.data.LugarModel" );
import ( "model.data.Lugar" );
import ( "model.data.LugarCollection" );

import ( "model.data.CostoDeTrasladoModel" );
import ( "model.data.CostoDeTraslado" );
import ( "model.data.CostoDeTrasladoCollection" );

$idlugar = $_POST ['lugar'];

$costos = array ();

$col = new LugarCollection ( $dbconnection );

$col->Load ( null, null, "*", 'creador = $1 AND id <> $2', array ($_SESSION ['user'], $idlugar ) );

$lugares = array ();

if (is_array ( $col->collection ))
	foreach ( $col->collection as $l ) {
		$lugares [$l->id] = $l;
		$costos [$l->id]->costo = 0;
		$costos [$l->id]->destino = $l->id;
	}

$col = new CostoDeTrasladoCollection ( $dbconnection );

$col->Load ( null, null, "*", 'origen = $1', array ($idlugar ) );

if (is_array ( $col->collection )) {
	foreach ( $col->collection as $item ) {
		$costos [$item->destino] = $item;
	}
}

include "../view/custom/form_edit_lugar_costos_de_traslado.tpl";
?>