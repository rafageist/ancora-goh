<?php
    echo "conecntando...\n";    
    $db = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=postgres");
    echo "eliminando...\n";
    pg_query($db, "DROP DATABASE ancoragoh;");
    echo "creando...\n";
    pg_query($db, "CREATE DATABASE ancoragoh;");
    pg_close($db);
?>