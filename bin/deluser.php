<?php

    $adminpath = "../";
    
    include "../lib/console.lib";

    $console = new Console();
    
    if (!$console->verifyCli())
        die("Esta aplicación solo se ejecuta en la línea de comandos. \n");
    
    $usuario = '';
    $pass = '';
    
    $usuario = $console->getStream("Nombre de usuario: ");
   
    include "../lib/strings.lib";
    include "../control/Connection.php";
    include "../modelo/PartModel.php";
    include "../modelo/UsuarioModel.php";
        
    $u = new UsuarioModel($dbconnection);
    
    $u->removeBy('nick = $1', false, array($usuario));
    
    $console->showMessage("Usuario eliminado satisfactoriamente.\n");

?>