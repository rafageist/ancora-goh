<?php

    import("model.data.PublicFunctions");
    
    $m = new PublicFunctions($dbconnection);
    
    $m->MueveOCreaBrigadaDeGrupo($_POST['brigada'],$_POST['grupo']);

?>