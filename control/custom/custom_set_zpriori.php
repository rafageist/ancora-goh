<?php

import ( "model.data.ZprioriModel" );
import ( "model.data.Zpriori" );
import ( "model.data.ZprioriCollection" );

$m = new ZprioriModel ( $dbconnection );

$idclasif = $_POST ['clasif'];

$zp = $_POST ['zpriori'];

$periodo = $_POST ['periodo'];

$m->removeBy ( 'periodo = $1 AND clasificacion = $2', false, array ($periodo, $idclasif ) );

$arr = explode ( ";", $zp );

$d = 0;
foreach ( $arr as $item ) {
	$d ++;
	$t = 0;
	$arr2 = explode ( ",", $item );
	
	foreach ( $arr2 as $subitem ) {
		$t ++;
		if ($subitem != '1')
			$m->add ( $idclasif, $periodo, $d, $t, $subitem );
	
	}
}

?>