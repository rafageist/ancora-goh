<?php
    echo "conecntando...\n";    
    $db = pg_connect("dbname=postgres"); // host/port/user/password from PGHOST, PGPORT, PGUSER, PGPASSWORD or ~/.pgpass
    echo "eliminando...\n";
    pg_query($db, "DROP DATABASE ancoragoh;");
    echo "creando...\n";
    pg_query($db, "CREATE DATABASE ancoragoh;");
    pg_close($db);
?>