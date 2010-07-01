<?php

    import("model.data.PublicFunctions");
    
    $m = new PublicFunctions($dbconnection);
    
    $m->BajaOrdenDePlantilla($_POST['plantilla']);

?>