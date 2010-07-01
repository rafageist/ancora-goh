<?php

$db = pg_connect ( "host=localhost port=5432 dbname=ancoragoh user=postgres password=postgres" );

if (isset ( $_POST ['generar'] ))
	pg_send_query ( $db, "select generar('especialidad100410_0745581270943158wvfiha5ogsa1txlv2tb42351cr0iwigw6dw14yc87dycf7ir9i80lxr91jejeytga99vl1e','nivel100410_0833321270946012hykmqnb9ekplbocfsf0yps5xe0ziosfhyvl11f1u9yu7femqy0hrni1ywlajhhw9s','periodo100410_05401312709356140cw9ia153ykmj2v81ssv4uo3vidqxg8edx1zsm7yasibf4djbtljkmiu0r1e1yenveilgupovguxo10q1pr4f0q',1,true,1,false,false);" )?>


	
<form action="test.php" method="POST">
	
	<input type="submit" value ="Generar" name="generar">
	
</form>