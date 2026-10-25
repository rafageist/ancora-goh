<?php

	import("model.data.EspecialidadModel");
	import("model.data.Especialidad");
	import("model.data.EspecialidadCollection");
	
	$col = new EspecialidadCollection($dbconnection);
	
	$col->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
	
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