<table>
<?php 
	if(is_array($profes)){
		foreach ($profes as $profe){
			echo "<tr><td><label style='cursor: pointer;' onclick = 'ancoragoh.client.browsers.horarios.showHorarioProfesor(\"".$profe['id']."\");'>
			<img src='images/profesor_lite.png'>".$profe['nombre']."</label>
			</td></tr>";
		}
	}
?>
</table>