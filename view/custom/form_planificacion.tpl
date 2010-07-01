<script>
 $(function(){
    $('.date-picker').datepicker({
            dateFormat: "dd/mm/yy",
            regional: "es",
            changeMonth: true,
            changeYear: true
        }); 
  });
</script>
<!-- Preproccessing data -->

<?php
    if (!isset($id)) $id = '';
    if (!isset($descripcion)) $descripcion = '';
    if (!isset($fecha_ini)) $fecha_ini = '';
    if (!isset($fecha_fin)) $fecha_fin = '';
?>

<!-- Delete confirmation frame -->

<?php if ($for_new == false) {?>
    <div id = "frame_del_Planificacion" class = "frame-delete">
        <table>
            <tr>
                <td><img src = "images/planificacion_delete.png"></td>
                <td>
                    Est&aacute; seguro que desea eliminar?<br>
                    <input type = "button" onclick = "ancoragoh.client.forms.planificacion.event.eliminar();" value = "Eliminar"> 
                    <input type = "button" onclick = "$('#frame_del_Planificacion').fadeOut('slow');" value = "Cancelar">
                </td>
            </tr>
        </table>
    </div>
<?php }?>

<!-- Data form -->

<input type = "text" id = "field_Planificacion_Id" value = "<?php echo $id; ?>">
<fieldset>
    <legend> Datos de la planificaci&oacute;n </legend>
    T&iacute;tulo: <br>
    <input type = "text" id = "<?php echo ($for_new==true)?"new":"edit"; ?>_Planificacion_Descripcion" class = "edt-title" value = "<?php echo $descripcion; ?>"> <br>
    Fecha inicial: <br>
    <input type = "text" id = "<?php echo ($for_new==true)?"new":"edit"; ?>_Planificacion_FechaInicial" id = "edtFechaIni" value = "<?php echo $fecha_ini; ?>" onkeypress = "return false" class = "date-picker"> <br>
    Fecha final: <br>
    <input type = "text" id = "<?php echo ($for_new==true)?"new":"edit"; ?>_Planificacion_FechaFinal" id = "edtFechaIni" value = "<?php echo $fecha_fin; ?>" onkeypress = "return false" class = "date-picker"> <br>
</fieldset>

<!-- Form buttons -->

<br>
<div class = "buttons">
	<input class = "button" type = "button" onclick = "<?php echo ($for_new==true)?'ancoragoh.client.forms.planificacion.event.agregar();':'ancoragoh.client.forms.planificacion.event.modificar();'; ?>" value = "Aceptar"> 
	<?php if ($for_new == false) {?>
	<input class = "button" type = "button" onclick = "$('#frame_del_Planificacion').fadeIn('slow');" value ="Eliminar" >
	<?php }?>
	<input class = "button" type = "button" onclick = "ancoragoh.client.forms.planificacion.event.cancelar();" value = "Cancelar">
</div>