<b>Profesores<b> <label
	onclick="ancoragoh.client.forms.pxact.showForNew('<?php
	echo $idplantilla;
	?>');"
	style="cursor: pointer" title="Nuevo profesor para esta actividad"><img
	src="images/profesor_add.png"></label>
<table width="100%">
	<?php
	$i = 0;
	foreach ( $profesores as $key => $profe ) {
		$i ++;
		echo '<tr class = "tr_lxact" id = "tr_lxact_' . $i . '"><td style="border: 1px dashed black;" class = "gray-high-light"><label style = "cursor: pointer;" onclick = "ancoragoh.client.forms.pxact.showForEdit(\'' . $key . '\');"><img src="images/profesor_lite.png">' . $profe->nombre . '</label>
			</td>
			<td class = "gray-high-light">
			 <label class = "delete" style = "curor:pointer;" title = "Editar prioridad" onclick = "ancoragoh.client.forms.pxact.editarPrioridadPxact(\'' . $profe->profesor. '\',\''.$key.'\','.$i.');">
                        ' . $profe->prioridad . '
                        </label>
			</td>
			<td>
			     <label class = "delete" onclick = "ancoragoh.client.forms.pxact.showFrameEliminarPxact(\'' . $key . '\',' . $i . ');" title = "Eliminar esta asignaci&oacute;n de profesor">
                   x
                 </label>
			</td>
			</tr>';
	}
	?>
</table>
<!-- Delete confirmation frame -->
<div id="frameEliminar-pxact" class='frame-delete'>
<table>
	<tr>
		<td><img src="images/profesor_delete.png"></td>
		<td>Est&aacute; seguro que desea eliminar?<br>
		<input type="button"
			onclick="ancoragoh.client.forms.pxact.event.eliminar();"
			value="Eliminar"> <input type="button"
			onclick="$('#frameEliminar-pxact').fadeOut('slow');" value="Cancelar">
		</td>
	</tr>
</table>
</div>
<script>
	function showFrameEliminarLxact(idlxact, ixlxact){        
		$("#idLxactToDelete").val(idlxact);
		$("#idxLxactToDelete").val(ixlxact);
		$("#frameEliminar-lxact").fadeIn("slow");
	}

	
</script>
<div id = "pxact_prioridad" style="height:200px; overflow: auto; width: 99%">
	
</div>