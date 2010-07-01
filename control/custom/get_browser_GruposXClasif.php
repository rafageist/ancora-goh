<?php

import("model.data.BrigadaDeGrupoModel");
import("model.data.ClasificacionDeActividadModel");

$idesp = $_POST['especialidad'];
$idnivel = $_POST['nivel'];

$brggrupomodel = new BrigadaDeGrupoModel($dbconnection);
$clasifmodel = new ClasificacionDeActividadModel($dbconnection);

$clasifs = $clasifmodel->getAllBy("creador = '{$_SESSION['user']}'");

include "../view/custom/browser_grupos.tpl";
    
?>