<?php

	import("model.data.NivelModel");
	import("model.data.Nivel");
	import("model.data.NivelCollection");
	
	$col = new NivelCollection($dbconnection);
	
	$col->Load(null,null,"creador = '{$_SESSION['user']}'");
	
	if (is_array($col->collection)){
		$i =0;
		echo '[';
		foreach($col->collection as $item){
			$i++;
			echo "{
			id:  '{$item->id}',
		    descripcion: '{$item->descripcion}'
			}";
			if ($i<count($col->collection)) echo ",";
		}
		echo ']';
	} else
	echo '[]';
	
?>