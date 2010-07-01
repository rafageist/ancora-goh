<table>
<?php 
	if(is_array($lugares)){
		foreach ($lugares as $lugar){
			echo "<tr><td><label style='cursor: pointer;' onclick = 'ancoragoh.client.browsers.horarios.showHorarioLugar(\"".$lugar['id']."\");'>
			<img src='images/lugar_lite.png'>".$lugar['nombre']."</label>
			</td></tr>";
		}
	}
?>
</table>