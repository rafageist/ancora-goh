<table>
<tr>
	<?php 
	
		if (is_array($planifs)){
			foreach ($planifs as $planif){
				echo "<td><label onclick='ancoragoh.client.browsers.desgloses.showDesgloseDetalle(\"".$idasig."\",\"".$planif['id']."\",\"desglosesDetalles\");' style='cursor:pointer'><img src='images/planificacion.png'><br>".$planif['descripcion'].'</label></td>';
			}
		}
	
	?>
</tr>
</table>