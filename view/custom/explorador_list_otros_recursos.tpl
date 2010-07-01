<table>
<?php 
	if(is_array($recursos)){
		foreach ($recursos as $recurso){
			echo "<tr><td><label style='cursor: pointer;' onclick = 'ancoragoh.client.browsers.horarios.showHorarioOtroRecurso(\"".$recurso['id']."\");>
			<img src='images/otro_recurso_lite.png'>".$recurso['nombre']."</label></td></tr>";
		}
	}
?>
</table>