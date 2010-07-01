<table>
<tr>
<?php
	foreach ($planifs as $planif)
		echo '<td align = "center"><img class="iconlink" src="images/planificacion.png" 
				onclick = "ancoragoh.client.browsers.organizacion.seleccionaPlanificacion(\''.$planif->id.'\');" >
			  <br>'.$planif->descripcion.'</td>';
?>
</tr>
</table>