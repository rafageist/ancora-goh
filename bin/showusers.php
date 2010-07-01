<?php

    $adminpath = "../";
    
    include "../lib/console.lib";

    $console = new Console();
    
    if (!$console->verifyCli())
        die("Esta aplicación solo se ejecuta en la línea de comandos. \n");
    
    $usuario = '';
    $pass = '';
    
    include "../lib/strings.lib";
    include "../control/Connection.php";
    include "../modelo/PartModel.php";
    include "../modelo/UsuarioModel.php";
    include "../modelo/Usuario.php";
    include "../modelo/UsuarioCollection.php";
        
    $users = new UsuarioCollection($dbconnection);

    $users->Load();
    
    if (is_array($users->collection)){
        echo "--------- \n";
        echo " #  Nick  \n";
        echo "--------- \n";
        $i = 0;
        foreach ($users->collection as $user){
            $i++;
            echo $i." - ".$user->nick."\n";
        }
    }
    
?>