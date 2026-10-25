<?php

	import("model.data.PlanificacionModel");
	import("model.data.Planificacion");
	import("model.data.PlanificacionCollection");
	
	import("model.data.PeriodoModel");
	import("model.data.Periodo");
	import("model.data.PeriodoCollection");
	
	$col = new PlanificacionCollection($dbconnection);
	
	$col->Load(null, null, "*", 'creador = $1', array($_SESSION['user']));
	
	if (is_array($col->collection)){
		$i = 0;
		echo '[';
		foreach($col->collection as $item){
			$i++;
			echo "{
			id:  '{$item->id}',
		    descripcion: '{$item->descripcion}',
		    periodos: [";
			
			$p = $item->getPeriodo();
			
			$j=0;
			if (is_array($p->collection)){
				foreach ($p->collection as $ip){
					$j++;
					echo "{id: '{$ip->id}', descripcion: '{$ip->descripcion}'}";
					if ($j<count($p->collection)) echo ",";
				}
			}
			echo "],
			cantPeriodos:";
			if (is_array($p->collection)) echo count($p->collection); else echo 0;
			echo "
			}";
			if ($i<count($col->collection)) echo ",";
		}
		echo ']';
	} else
	echo '[]';
	
?>