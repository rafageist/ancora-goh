<script>
<?php 

	$min = 1;
	$max = 1;
	
	$primero = true;
	
	if (is_array($planifs->collection)) foreach ($planifs->collection as $planif){
		if ($min > $planif->fecha_inicial || $primero == true) $min = $planif->fecha_inicial;
		if ($max < $planif->fecha_inicial + $planif->cant_dias - 1 || $primero == true) $max = $planif->fecha_inicial + $planif->cant_dias - 1;
		$primero = false;	
	}
	
	
	$dini = TimeUtils::number_to_date($min);
	$dfin = TimeUtils::number_to_date($max);
	
	$ini = $dini->a.(($dini->m < 10)?"0":"").$dini->m.(($dini->d < 10)?"0":"").$dini->d;
	$fin = $dfin->a.(($dfin->m < 10)?"0":"").$dfin->m.(($dfin->d < 10)?"0":"").$dfin->d;
	
	echo '$(function(){
			$("#gantt_planif").gantt({
				\'tasks\':[';
					if (is_array($planifs->collection)){
						$primero = true;
						foreach ($planifs->collection  as $planif){
							$d = $planif->descripcion;
							
							$fi =  TimeUtils::number_to_date($planif->fecha_inicial);
							$ff =  TimeUtils::number_to_date($planif->fecha_inicial+ $planif-> cant_dias -1);
							
							$di = $fi->a.(($fi->m < 10)?"0":"").$fi->m.(($fi->d < 10)?"0":"").$fi->d;
							$df = $ff->a.(($ff->m < 10)?"0":"").$ff->m.(($ff->d < 10)?"0":"").$ff->d;
							
							if ($primero == false) echo ",";
							
							echo "{ 'titles': '$d', 'start_date': '$di', 'end_date': '$df' }";
							
							$primero = false;
						}
					}
	echo ']
		});
		$("#gantt_planif").setPeriod(\''.$ini.'\',\''.$fin.'\');
	});';
?>  
</script>

<div id = "diagrama_gantt" class = "hidden">
	<div id = "gantt_planif" >
	</div>
</div>