<?php

    $adminpath = "../";
    
    include "../lib/console.lib";

    $console = new Console();
    
    if (!$console->verifyCli())
        die("Esta aplicacion solo se ejecuta en la linea de comandos. \n");
    
    $nombre = '';
    $info = '';
    $correo = '';
    $momento_ingreso = "tiempo.moment_now()";
    $nick = '';
    $pass = '';
    
    if (isset($console->arguments['-nombre'])) $nombre = trim($console->arguments['-nombre']);
    if (isset($console->arguments['-info'])) $info = trim($console->arguments['-info']);
    if (isset($console->arguments['-correo'])) $info = trim($console->arguments['-correo']);
    if (isset($console->arguments['-nick'])) $nick = trim($console->arguments['-nick']);
    if (isset($console->arguments['-pass'])) $pass = trim($console->arguments['-pass']);
    
    if (trim($nick)!='' && trim($pass!='')){
        
        include "../lib/strings.lib";
        include "../control/Connection.php";
        include "../modelo/PartModel.php";
        include "../modelo/UsuarioModel.php";
        include "../modelo/Usuario.php";
        
        $u = new Usuario($nombre,$info,$correo, $momento_ingreso, $nick, $pass);

        $console->showMessage("Usuario agregado satisfactoriamente.\n");
    }
     else
       $console->showMessage("Especifique el nombre de usuario y contrasena.\n");
    
?>