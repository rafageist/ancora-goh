<div onmousemove = "$('#imessage').fadeOut('slow');" style = "padding: 5px; background: #EEEEEE; width: 100%; height: 250px; overflow: auto; border: 1px solid black;">
<b>Otros recursos asignados<b>
<table width = "100%">
<tr><td>#</td><td>P</td><td>I</td><td>Recursos</td><td></td><tr>
	<?php
	    $i = 0;
		if (is_array($rxact)) foreach ($rxact as $rc) {
			$i++;			
			echo '<tr class = "tr_rxact" id = "tr_rxact_'.$i.'" class = "gray-high-light">
                     <td style="border: 1px dashed black;" class = "gray-high-light" width = 40>
                        <label style="cursor: pointer" onclick = "editarGrupoRecursos(\''.$rc['id'].'\','.$i.');"><img src="images/otro_recursos_lite.png">'.$i.'</label>
                     </td>
                     <td>
                        <label class = "delete" style = "curor:pointer;" title = "Editar prioridad" onclick = "editarPrioridad(\''.$rc['id'].'\',\''.$i.'\');">'
                        .$rc['prioridad'].'
                        </label>
                     </td>
                    
                     <td>
                     
                     <input id ="gr_imp_'.$i.'" type="checkbox" onchange = "ancoragoh.client.forms.rxact.setImprescindibles(\''.$rc['id'].'\', this.id);" '.
                     (($rc['imprescindibles']==true)?"checked":"").'
                     ></td>
                     <td>
                     	<table>
                     		<tr>
                     			';
                    
                     if (is_array($recursos[$rc['id']]))
                     foreach ($recursos[$rc['id']] as $recurso){
                     	echo '<td align = "center">';
                     	                     	echo '<label class = "delete" style = "curor:pointer;" title = "Eliminar esta asignaci&oacute;n de recurso" onclick = "ancoragoh.client.forms.rxact.showFrameEliminarRxAct(\''.$rc['id'].'\',\''.$recurso->id.'\');">x</label><br>';
                     	echo '<img src = "images/otro_recurso_lite.png"><br>';
                     	echo '<label class = "delete" style = "curor:pointer;" title = "Editar prioridad" onclick = "">'.$recurso->prioridad.'</label>';

                        echo '<br>'.$recurso->nombre;
                     	echo '</td>';
                     }
                     
                     echo '<td><label class = "iconlink opaco" onclick = "ancoragoh.client.forms.rxact.showAddRecursos(\''.$rc['id'].'\');" title = "Agregar recursos a este grupo de recursos" ><img src = "images/add_lite.png"></label></td></tr>

                     <tr>
                     	';
                     
                     $c = count($recursos[$rc['id']]);                        
                     					 
					 for($j=1; $j <= $c; $j++){
					 	echo '<td style = "background: '.(($j<=$rc['necesarios'])?'#AAAAAA':'transparent').'; "><label class = "iconlink" onclick = "ancoragoh.client.forms.rxact.setNecesariosGRxAct(\''.$rc['id'].'\','.$j.');">'.$j.'</td>';
					 }
					 	
                     echo '
                     </tr>
                     
                     </table>
                     </td>
                     <td>
                        <label class = "delete" onclick = "ancoragoh.client.forms.rxact.showFrameEliminarGRxAct(\''.$rc['id'].'\');" title ="Eliminar esta asignaci&oacute;n de grupo de recursos">
                            x
                        </label>
                     </td>
                 </tr>';
		}
	?>
</table>
<label onclick = "ancoragoh.client.forms.rxact.agregarGrupoRecursos();" class = "iconlink opaco" title = "Nuevo grupo de recursos para esta actividad"><img src="images/add_lite.png"></label>
</div>
<div id = "frame_del_GRxAct" class = "frame-delete">
<table>
	<tr>
		<td><img src="images/otro_recurso_delete.png"></td>
		<td>Est&aacute; seguro que desea eliminar?<br>
		<input type="button" onclick="ancoragoh.client.forms.rxact.eliminarGRxAct();" value="Eliminar"> 
		<input type="button" onclick="$('#frame_del_GRxAct').fadeOut('slow');" value="Cancelar">
		</td>
	</tr>
</table>
</div>
<div id = "frame_del_RxAct" class = "frame-delete">
<table>
	<tr>
		<td><img src="images/otro_recurso_delete.png"></td>
		<td>Est&aacute; seguro que desea eliminar?<br>
		<input type="button" onclick="ancoragoh.client.forms.rxact.eliminarRxAct();" value="Eliminar"> 
		<input type="button" onclick="$('#frame_del_RxAct').fadeOut('slow');" value="Cancelar">
		</td>
	</tr>
</table>
</div>
<input type="hidden" value="" id="idRxactToDelete">
<input type="hidden" value="" id="idGRxactToDelete">
<script>
	function editarPrioridad(idrxact, idx){
		var idplantilla = '<?php echo $idplantilla;?>';       
		ancoragoh.client.forms.rxact.editarPrioridadesRxAct(idplantilla,idrxact,'rxact_prioridad');
	}
</script>
<div id = "rxact_prioridad" style="height:200px; overflow: auto; width: 99%">
	
</div>