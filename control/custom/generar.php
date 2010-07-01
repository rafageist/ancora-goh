<?php

set_time_limit ( 0 );

import ( "model.data.PublicFunctions" );

$idesp = $_POST ['esp'];
$idnivel = $_POST ['nivel'];
$idperiodo = $_POST ['periodo'];
$forma = $_POST ['forma'];
$parche = $_POST ['parche'];
$compactar = $_POST ['compactar'];
$parche1 = $_POST ['parche1'];
$buscalugar = $_POST ['buscalugar'];

$g = new PublicFunctions ( $dbconnection );

$g->Generar ( $idesp, $idnivel, $idperiodo, $forma, $parche, $compactar, $parche1, $buscalugar );


?>