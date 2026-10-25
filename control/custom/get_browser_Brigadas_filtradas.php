<?php

import("model.data.BrigadaModel");
import("model.data.Brigada");
import("model.data.BrigadaCollection");
   
$brigadas = new BrigadaModel($dbconnection);
$arr = $brigadas->getAllBy('especialidad = $1 AND nivel = $2 ORDER BY descripcion', "*", false, array($_POST['especialidad'], $_POST['nivel']));
include "../view/custom/brigadas_filtradas.tpl";

?>