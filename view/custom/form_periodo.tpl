<!-- Preproccessing data -->
<?php
    if (!isset($id)) $id = '';
    if (!isset($descripcion)) $descripcion = '';
    if (!isset($cant_dias)) $cant_dias = 5;
    if (!isset($cant_turnos)) $cant_turnos = 6;
    if (!isset($hora_inicial)) $hora_inicial = TimeUtilsCommon::hour_to_number(8,0,0);
    if (!isset($duracion_del_turno)) $duracion_del_turno = TimeUtilsCommon::hour_to_number(1,30,0);
    if (!isset($planificacion)) $planificacion = "";
?>

<!-- Delete confirmation frame -->
<?php if ($for_new == false) {?>
    <div id = "frame_del_Periodo" class = "frame-delete">
    <table>
        <tr>
            <td><img src="images/periodo_delete.png"></td>
            <td>
                Est&aacute; seguro que desea eliminar?
                <br>
                <input type = "button" onclick = "ancoragoh.client.forms.periodo.event.eliminar();" value = "Eliminar"> 
                <input type = "button" onclick = "$('#frame_del_Periodo').fadeOut('slow');" value = "Cancelar">
            </td>
        </tr>
    </table>
    </div>
<?php }?>

<!-- Data form -->

<input type = "hidden" id = "field_Periodo_Id" value = "<?php echo $id; ?>">

<div id = "div_datos_periodo">
    <fieldset>
        <legend> Datos de la planificaci&oacute;n </legend>
        <?php if (isset($idplanif)) {?>
			<input type = "hidden" id = 'field_Periodo_Planificacion' value = "<?php echo $idplanif; ?>" >
        <?php }?>
		<?php if ($for_new == false) {?>
			<input type = "hidden" id = 'data_cant_dias_planificacion' value = "<?php echo $cant_dias_planificacion; ?>" >
        	<input type = "hidden" id = 'data_id_periodo' value = "<?php echo $id; ?>" >
        <?php }?>
        
        Descripci&oacute;n: <br>
        <input type = "text" id = "field_Periodo_Descripcion" class="edt-title" value="<?php echo $descripcion; ?>"><br>
        Cantidad de d&iacute;as: <br>
        <input type="text" id = "field_Periodo_Cant_dias" class="" value="<?php echo $cant_dias; ?>"><br>
        Cantidad de turnos: <br>
        <input type="text" id = "field_Periodo_Cant_turnos" class="" value="<?php echo $cant_turnos; ?>"><br>
        Hora inicial: <br>
        <select id = "field_Periodo_Hora_inicial">
            <?php
            for ($h = 0; $h <24; $h++)
                for ($m = 0; $m < 60; $m++){
                    $n = TimeUtilsCommon::hour_to_number($h, $m, 0);
                    echo '<option value ="'.$n.'" '.($hora_inicial==$n?"selected":"").'>'.($h<10?"0":"").$h.":".($m<10?"0":"").$m." hrs </option>";
                }
            ?>
        </select>
        <br>
        Duraci&oacute;n del turno: <br>
        <select id = "field_Periodo_Duracion_del_turno">
            <?php
            for ($h = 0; $h <24; $h++)
            for ($m = 0; $m < 60; $m++){
                $n = TimeUtilsCommon::hour_to_number($h, $m, 0);
                echo '<option value = "'.$n.'" '.($duracion_del_turno==$n?"selected":"").'>'.($h<10?"0":"").$h.":".($m<10?"0":"").$m." hrs </option>";
            }
            ?>
        </select>       
    <fieldset>
</div>

<div class = "buttons">
	<input class = "button" class = "iconlink" type="button" onclick="<?php echo ($for_new == true)?'ancoragoh.client.forms.periodo.event.agregar();':'ancoragoh.client.forms.periodo.event.modificar();'; ?>" value="Aceptar"> 
	<?php if ($for_new == false) {?>
	    <input class = "button" class = "iconlink" type = "button" onclick = "$('#frame_del_Periodo').fadeIn('slow');" value = "Eliminar">
	<?php }?>        
	<input  class = "button" class = "iconlink" type = "button" onclick = "ancoragoh.client.forms.periodo.event.cancelar();" value = "Cancelar">
</div>
