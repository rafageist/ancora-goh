<?php

import ( "model.data.CostoDeTrasladoModel" );

$m = new CostoDeTrasladoModel ( $dbconnection );

$origen = $_POST ['origen'];
$destino = $_POST ['destino'];
$costo = $_POST ['costo'];

$m->removeBy ( "origen = '$origen' AND destino = '$destino'" );
if ($costo != 0)
	$m->add ( $origen, $destino, $costo );

?>