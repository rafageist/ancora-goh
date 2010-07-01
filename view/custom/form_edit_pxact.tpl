<input type = "hidden" id = "flag_PxAct_cantGrupos" value = "<?php echo count($grupos); ?>">
<input type = "hidden" id = "flag_PxAct_idPxact" value = "<?php echo $idpxact; ?>">
<input type = "hidden" id = "flag_PxAct_profesor" value = "<?php echo $pxact->profesor; ?>">

<b>Detalles del profesor asignado</b>
<div style = "width:250px; height:200px; text-align: center; overflow: auto; background: #DDDDDD; border: 1px solid #AAAAAA;padding: 2px;margin: auto;">
<br>
Prioridad <br> 
<select id = "field_PxAct_prioridad">
<?php

    for($i=1; $i <= $max_priority + 1; $i++){
    	$s= "";
    	if ($i == $pxact->prioridad) $s = "selected";
    	echo '<option value = "'.$i.'" '.$s.'>'.$i.'</option>';
    }
?>
</select>

<br>
Grupos que atiende:<br>
<table>
<?php

    $i = 0;
	if(is_array($grupos))
    foreach ($grupos as $grupo){
    	$i++;
    	$chk = '';
    	if ($grupo['checked'] == true) $chk = 'checked';
    	
        echo '<tr><td><input type="checkbox" id = "chkGrupoDeProfesor_'.$i.'" '.$chk.'><input type="hidden" id="edtIdGrupoDeProfesor_'.$i.'" value="'.$grupo['id'].'"></td><td align="center"><br><img src="images/grupo.png"><br>'.$grupo['descripcion']."</td>";
        echo "<td><table><tr>";
        if (is_array($brigadas[$grupo['id']]))
        	foreach($brigadas[$grupo['id']] as $brg){
        		echo "<td align='center' style='border: 1px dashed #AAAAAA;'><img src='images/brigada_lite.png'><br>".$brg['descripcion']."</td>";
        	}
        echo "</tr></table></td>";
        echo "</tr>";
    }
    else
    echo "No ha definido grupos para la clasificacion de la plantilla de actividad seleccionada."
?>
</table>
</div>

<!-- Buttons -->
<br>
<div class = "buttons">
	<input class = "button" type = "button" value = "Guardar" onclick = "ancoragoh.client.forms.pxact.event.modificar();">
	<input class = "button" type = "button" value = "Cancelar" onclick = "ancoragoh.client.forms.pxact.event.cancelar();">
</div>