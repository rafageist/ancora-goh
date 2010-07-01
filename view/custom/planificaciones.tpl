<?php 

	import("lib.ancora.timeutils.TimeUtils");	
	
	?>
	<img class="iconlink" src="images/planificacion_add.png" title="Nueva planificaci&oacute;n" onclick = "ancoragoh.client.forms.planificacion.showForNew();">
	
	<?php 
	echo '<div id="browserPlanficaciones"><table>';

	$min = 1;
	$max = 1;
	
	$primero = true;
	
	if (is_array($planifs->collection)) foreach ($planifs->collection as $planif){
		if ($min > $planif->fecha_inicial || $primero == true) $min = $planif->fecha_inicial;
		if ($max < $planif->fecha_inicial + $planif->cant_dias - 1 || $primero == true) $max = $planif->fecha_inicial + $planif->cant_dias - 1;
		$primero = false;	
	}
	
	$first_day = $min;
	$last_day = $max;
	 /*
	echo "<tr><td></td>";
		if (is_array($planifs->collection)) for($i = $first_day; $i <= $last_day; $i++){
			$fecha = TimeUtils::number_to_date($i);
			echo "<td>{$fecha->a}</td>";
		}
	echo "</tr>";
	
	echo "<tr><td></td>";
		if (is_array($planifs->collection)) for($i = $first_day; $i <= $last_day; $i++){
			$fecha = TimeUtils::number_to_date($i);
			echo "<td>{$fecha->m}</td>";
		}
	echo "</tr>";
	
	echo "<tr><td></td>";
		if (is_array($planifs->collection)) for($i = $first_day; $i <= $last_day; $i++){
			$fecha = TimeUtils::number_to_date($i);
			echo "<td>{$fecha->d}</td>";
		}
	echo "</tr>";
    */
	if (is_array($planifs->collection))
	foreach ($planifs->collection  as $planif){
        //<img src='images/planificacion.png'>
		echo "<tr><td><label style = 'width: 100px;' class = \"iconlink\" onclick =\"application.forms.editPlanificacion('{$planif->id}')\">  {$planif->descripcion}</label></td>";
		for($i = $first_day; $i <= $last_day; $i++){
			echo "<td>";
            $f = $fecha = TimeUtils::number_to_date($i);
            $fecha = $f->asString();
			if ($i >= $planif->fecha_inicial && $i <= $planif->fecha_inicial + $planif->cant_dias)
				echo "<div style=\"width:100%;height:10px;background: blue;\" title= \"$fecha\"> &nbsp; </div>";
			else echo "&nbsp;";
			echo "</td>";
		}
		echo "</tr>";
	}
	echo "</table></div>";


?>