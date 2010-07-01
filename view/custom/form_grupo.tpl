
<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
    <div id = "frame_del_Grupo" class = "frame-delete">
        <table>
            <tr>
                <td><img src = "images/grupo_delete.png"></td>
                <td>
                    Est&aacute; seguro que desea eliminar?<br>
                    <input type = "button" onclick = "ancoragoh.client.forms.grupo.event.eliminar();" value = "Eliminar"> 
                    <input type = "button" onclick = "$('#frame_del_Grupo').fadeOut('slow');" value = "Cancelar">
                </td>
            </tr>
        </table>
    </div>
<?php }?>

<!-- Form buttons -->

<br>

<?php if ($for_new == false) {?>
	<input id = "field_Grupo_Id" type = "hidden" value ="<?php echo $id; ?>">
<?php }?>

<input id = "field_Grupo_Descripcion" type = "text" value ="<?php echo $descripcion; ?>">
<br>
<div class = "buttons">
<input class = "button" type="button" onclick="<?php echo ($for_new == true)?'ancoragoh.client.forms.grupo.event.agregar();':'ancoragoh.client.forms.grupo.event.modificar();'; ?>" value="Aceptar"> 
<?php if ($for_new == false) {?>
    <input class = "button" type = "button" onclick = "$('#frame_del_Grupo').fadeIn('slow');" value = "Eliminar">
<?php }?>        
<input class = "button" type="button" onclick="cancelar();" value="Cancelar">    
</div>