<?php

$db = pg_connect ( "dbname=ancoragoh" ); // credentials from PGHOST, PGPORT, PGUSER, PGPASSWORD or ~/.pgpass

$r = pg_fetch_all ( pg_query ( "select * from tempo;" ) );
print_r ( $r );

?>