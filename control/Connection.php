<?php
    
    $dbcfg = new PropertiesFile("../bin/ancoragoh.conf");
    
    $dbconnection = new PostgresDatabaseImpl();
    
    $dbconnection->Connect($dbcfg->dbname,$dbcfg->user,$dbcfg->password, $dbcfg->host, $dbcfg->port);
    
?>