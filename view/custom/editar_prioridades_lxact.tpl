<b>Establecer la prioridad para el lugar seleccionado<b>
<table>
<tr>
<?php 
	for($i = 1; $i <= $max_priority + 1; $i++){
		echo "<td style='border: 1px dashed #AAAAAA' ><label class='delete' style='cursor: pointer;' onclick='ancoragoh.client.browsers.desgloses.actualizaPrioridad($i,\"$idlugar\");'> &nbsp; ".$i." &nbsp;</label></td>";
	}
?>
</tr>
</table>
<input type="button" value="Cancelar" onclick ="ancoragoh.client.browsers.desgloses.cancelarPrioridad();">