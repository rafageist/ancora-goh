<?php

    import("model.data.PublicFunctions");
    
    $m = new PublicFunctions($dbconnection);
    
    $m->SubeOrdenDePlantilla($_POST['plantilla']);

?>