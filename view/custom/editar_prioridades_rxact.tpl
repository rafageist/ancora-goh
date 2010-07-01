<b>Establecer la prioridad para el grupo de recursos seleccionado<b>
<table>
<tr>
<?php 
	for($i = 1; $i <= $max_priority + 1; $i++){
		echo "<td style='border: 1px dashed #AAAAAA' >
		<label class='delete' style='cursor: pointer;' 
		onclick='ancoragoh.client.forms.rxact.actualizaPrioridadGRxAct($i);'> &nbsp; ".$i." &nbsp;
		</label></td>";
	}
?>
</tr>
</table>
<input type="button" value="Cancelar" onclick ="ancoragoh.client.forms.rxact.cancelarPrioridadGRxAct();">
