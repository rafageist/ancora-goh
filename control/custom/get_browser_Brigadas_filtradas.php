<?php

import("model.data.BrigadaModel");
import("model.data.Brigada");
import("model.data.BrigadaCollection");
   
$brigadas = new BrigadaModel($dbconnection);
$arr = $brigadas->getAllBy("especialidad ='{$_POST['especialidad']}' AND nivel='{$_POST['nivel']}' ORDER BY descripcion");
include "../view/custom/brigadas_filtradas.tpl";

?>