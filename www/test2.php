<?php

$db = pg_connect ( "host=localhost port=5432 dbname=ancoragoh user=postgres password=postgres" );

$r = pg_fetch_all ( pg_query ( "select * from tempo;" ) );
print_r ( $r );

?>