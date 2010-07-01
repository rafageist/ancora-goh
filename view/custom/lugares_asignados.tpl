<div onmousemove = "$('#imessage').fadeOut('slow');" style = "padding: 5px; background: #EEEEEE; width: 100%; height: 250px; overflow: auto; border: 1px solid black;">
<b>Lugares asignados<b>
<label onclick="ancoragoh.client.forms.lxact.showForNew('<?php count($lugares_disponibles); ?>','<?php echo $idplantilla; ?>');" style = "cursor: pointer;" title = "Nuevos lugares para esta actividad"><img src="images/lugar_add.png"></label>
<table width = "100%">
	<?php
	    $i = 0;
		foreach ($lugs as $key=>$lug) {
			$i++;			
			echo '<tr class = "tr_lxact" id = "tr_lxact_'.$i.'" >
                     <td style="border: 1px dashed black;" class = "gray-high-light">
                        <img src="images/lugar_lite.png">'.$lug->nombre.'
                     </td>
                     <td class = "gray-high-light">
                        <label class = "delete" style = "curor:pointer;" title = "Editar prioridad" onclick = "editarPrioridad(\''.$lug->id.'\','.$i.');">'
                        .$prioridades[$lug->id].'
                        </label>
                     </td>
                     <td>
                        <label class = "delete" onclick = "showFrameEliminarLxact(\''.$lug->id.'\','.$i.');" title ="Eliminar esta asignaci&oacute;n de lugar">
                            x
                        </label>
                     </td>
                 </tr>';
		}
	?>
</table>
</div>
<!-- Delete confirmation frame -->
<div id = "frameEliminar-lxact" class = 'frame-delete'>
<table>
	<tr>
		<td><img src="images/lugar_delete.png"></td>
		<td>Est&aacute; seguro que desea eliminar?<br>
		<input type="button" onclick="ancoragoh.client.forms.lxact.event.eliminar();" value="Eliminar"> 
		<input type="button" onclick="$('#frameEliminar-lxact').fadeOut('slow');" value="Cancelar">
		</td>
	</tr>
</table>
</div>

<input type="hidden" value="" id="idLxactToDelete">
<input type="hidden" value="" id="idxLxactToDelete">
<script>
	function showFrameEliminarLxact(idlxact, ixlxact){        
		$("#idLxactToDelete").val(idlxact);
		$("#idxLxactToDelete").val(ixlxact);
		$("#frameEliminar-lxact").fadeIn("slow");
	}

	function editarPrioridad(idlugar, idx){
		var idplantilla = '<?php echo $idplantilla;?>';
        $('#tr_lxact_' + idx).css("background","#AAAAAA");
        $('#tr_lxact_' + idx).css("color","white");
		ancoragoh.client.browsers.desgloses.editarPrioridadesLxAct(idplantilla,idlugar,'lxact_prioridad');
	}
</script>
<div id = "lxact_prioridad" style="height:200px; overflow: auto; width: 99%">
	
</div>