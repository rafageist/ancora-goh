<?php

    $adminpath = "../";
    
    include "../lib/console.lib";

    $console = new Console();
    
    if (!$console->verifyCli())
        die("Esta aplicación solo se ejecuta en la línea de comandos. \n");
    
    $usuario = '';
    $pass = '';
     
    $id = $console->getStream("GUID de usuario: ");
       
    include "../lib/strings.lib";
    include "../control/Connection.php";
    include "../modelo/PartModel.php";
    include "../modelo/UsuarioModel.php";
        
    $u = new UsuarioModel($dbconnection);
    
    $u->remove($id);
    
    $console->showMessage("Usuario eliminado satisfactoriamente.\n");

?>