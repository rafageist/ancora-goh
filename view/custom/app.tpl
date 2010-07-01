<body onload="begin();">

<?php

$windows = array ();

import ( "lib.basic.file.DirectoryUtil" );

$wins = DirectoryUtil::getFilesWithExtension ( "../view/wins", "php" );

foreach ( $wins as $win )
	include "../view/wins/" . $win;

$windows [] = array ("t" => "Generador de horarios", "id" => "generador", "ico_lite" => "generador.png", "ico" => "generador.png", "resizable" => false, "draggable" => true, "height" => "400px", "width" => "670px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

/* Formularios */

// planificaciones
$windows [] = array ("t" => "Nueva planificaci&oacute;n", "id" => "new_planificacion", "ico_lite" => "planificacion_add_lite.png", "ico" => "planificacion_add.png", "resizable" => false, "draggable" => true, "height" => "200px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando planificaci&oacute;n", "id" => "edit_planificacion", "ico_lite" => "planificacion_edit_lite.png", "ico" => "planificacion_edit.png", "resizable" => false, "draggable" => true, "height" => "200px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

//especialidades
$windows [] = array ("t" => "Nueva especialidad", "id" => "new_especialidad", "ico_lite" => "especialidad_add_lite.png", "ico" => "especialidad_add.png", "resizable" => false, "draggable" => true, "height" => "150px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando especialidad", "id" => "edit_especialidad", "ico_lite" => "especialidad_edit_lite.png", "ico" => "especialidad_edit.png", "resizable" => false, "draggable" => true, "height" => "150px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
//periodos
$windows [] = array ("t" => "Nuevo per&iacute;odo", "id" => "new_periodo", "ico_lite" => "periodo_add_lite.png", "ico" => "periodo_add.png", "resizable" => false, "draggable" => true, "height" => "295px", "width" => "350px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando per&iacute;odo", "id" => "edit_periodo", "ico_lite" => "periodo_edit_lite.png", "ico" => "periodo_edit.png", "resizable" => false, "draggable" => true, "height" => "400px", "width" => "450px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando periodicidades del per&iacute;odo", "id" => "edit_periodo_periodicidad", "ico_lite" => "periodo_edit_lite.png", "ico" => "periodicidad.png", "resizable" => false, "draggable" => true, "height" => "400px", "width" => "450px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando restricciones del per&iacute;odo", "id" => "edit_periodo_restricciones", "ico_lite" => "periodo_edit_lite.png", "ico" => "periodo_edit.png", "resizable" => false, "draggable" => true, "height" => "400px", "width" => "450px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

//niveles
$windows [] = array ("t" => "Nuevo nivel", "id" => "new_nivel", "ico_lite" => "nivel_add_lite.png", "ico" => "nivel_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando nivel", "id" => "edit_nivel", "ico_lite" => "nivel_edit_lite.png", "ico" => "nivel_edit.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

// brigadas
$windows [] = array ("t" => "Nueva brigada", "id" => "new_brigada", "ico_lite" => "brigada_add_lite.png", "ico" => "brigada_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando brigada", "id" => "edit_brigada", "ico_lite" => "brigada_edit_lite.png", "ico" => "brigada_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

// clasificion de actividad
$windows [] = array ("t" => "Nueva clasificaci&oacute;n de actividad", "id" => "new_clasifact", "ico_lite" => "clasifact_add_lite.png", "ico" => "clasifact_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando clasificaci&oacute;n de actividad", "id" => "edit_clasifact", "ico_lite" => "clasifact_edit_lite.png", "ico" => "clasifact_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Zonas de prioridad", "id" => "edit_zpriori", "ico_lite" => "zpriori_lite.png", "ico" => "zpriori.png", "resizable" => false, "draggable" => true, "height" => "400px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// asignaturas
$windows [] = array ("t" => "Nueva asignatura", "id" => "new_asignatura", "ico_lite" => "asignatura_add_lite.png", "ico" => "asignatura_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando asignatura", "id" => "edit_asignatura", "ico_lite" => "asignatura_edit_lite.png", "ico" => "asignatura_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// grupos
$windows [] = array ("t" => "Nuevo grupo", "id" => "new_grupo", "ico_lite" => "grupo_add_lite.png", "ico" => "grupo_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando grupo", "id" => "edit_grupo", "ico_lite" => "grupo_edit_lite.png", "ico" => "grupo_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

// profesores
$windows [] = array ("t" => "Nuevo profesor", "id" => "new_profesor", "ico_lite" => "profesor_add_lite.png", "ico" => "profesor_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando profesor", "id" => "edit_profesor", "ico_lite" => "profesor_edit_lite.png", "ico" => "profesor_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// profesores para actividad
$windows [] = array ("t" => "Nuevo profesor para actividad", "id" => "new_pxact", "ico_lite" => "profesor_add_lite.png", "ico" => "profesor_add.png", "resizable" => false, "draggable" => true, "height" => "250px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando profesor para actividad", "id" => "edit_pxact", "ico_lite" => "profesor_edit_lite.png", "ico" => "profesor_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );

// otros recursos
$windows [] = array ("t" => "Nuevo recurso gen&eacute;rico", "id" => "new_otrorecurso", "ico_lite" => "otro_recurso_add_lite.png", "ico" => "otro_recurso_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando recurso gen&eacute;rico", "id" => "edit_otrorecurso", "ico_lite" => "otro_recurso_edit_lite.png", "ico" => "otro_recurso_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// otros recursos para actividad
$windows [] = array ("t" => "Editando recurso gen&eacute;rico", "id" => "new_rxact", "ico_lite" => "otros_recursso_lite.png", "ico" => "otros_recursos.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// lugares
$windows [] = array ("t" => "Nuevo lugar", "id" => "new_lugar", "ico_lite" => "lugar_add_lite.png", "ico" => "lugar_add.png", "resizable" => false, "draggable" => true, "height" => "170px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando lugar", "id" => "edit_lugar", "ico_lite" => "lugar_edit_lite.png", "ico" => "lugar_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Editando costos de traslado", "id" => "edit_costotraslado", "ico_lite" => "lugar_edit_lite.png", "ico" => "lugar_edit.png", "resizable" => false, "draggable" => true, "height" => "300px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// lugares para actividad
$windows [] = array ("t" => "Asignar lugares", "id" => "new_lxact", "ico_lite" => "lugar_add_lite.png", "ico" => "lugar_add.png", "resizable" => false, "draggable" => true, "height" => "250px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// desglose
$windows [] = array ("t" => "Propiedades del desglose", "id" => "edit_desglose", "ico_lite" => "desglose_edit_lite.png", "ico" => "desglose_edit.png", "resizable" => false, "draggable" => true, "height" => "250px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
// plantilla
$windows [] = array ("t" => "Propiedades de la plantilla", "id" => "edit_plantilla", "ico_lite" => "plantilla_edit_lite.png", "ico" => "plantilla_edit.png", "resizable" => false, "draggable" => true, "height" => "250px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
$windows [] = array ("t" => "Nueva platilla de actividad", "id" => "new_plantilla", "ico_lite" => "plantilla_add_lite.png", "ico" => "plantilla_add.png", "resizable" => false, "draggable" => true, "height" => "250px", "width" => "300px", "closeable" => true, "minimizable" => false, "maximizable" => false, "modal" => true );
?>

<script>
	$(window).resize(function(){
		// dimensiones de la ventana del explorer 
		var wscr = $(window).width();
		var hscr = $(window).height();
	
		// estableciendo dimensiones de fondo
		$('#bgtransparent').css("width", wscr);
		$('#bgtransparent').css("height", hscr);
		
		// estableciendo tamaño de la ventana modal
		/*$('#bgmodal').css("width", ancho+'px');
		$('#bgmodal').css("height", alto+'px');
		*/
		// obtiendo tamaño de la ventana modal
		var wcnt = $('#bgmodal').width();
		var hcnt = $('#bgmodal').height();
		
		// obtener posicion central
		var mleft = ( wscr - wcnt ) / 2;
		var mtop = ( hscr - hcnt ) / 2;
		
		// estableciendo ventana modal en el centro
		$('#bgmodal').css("left", mleft+'px');
		$('#bgmodal').css("top", mtop+'px');
	});
		<?php
		echo '
    $(function() {
        $(".ventana").draggable({handle: "h2", containment: "body"});
        $(".resizable").resizable({animate: true, ghost: true});        
		';
		
		$i = 0;
		foreach ( $windows as $win ) {
			$id = $win ['id'];
			$h = $win ['height'];
			$w = $win ['width'];
			
			if (! isset ( $win ['modal'] ))
				$modal = false;
			else
				$modal = $win ['modal'];
			
			echo "ancoragoh.client.windows[" . $i . "] = {id: '" . $id . "', " . 'height: "' . $h . '", width: "' . $w . '", status: "normal", left: "10px", top: "10px", showed: false, cerrando: false, modal: ' . ($modal == true ? "true" : "false") . '};' . "\n";
			echo '$("#win_' . $win ['id'] . '").css("width","' . $w . '");' . "\n";
			echo '$("#win_' . $win ['id'] . '").css("height","' . $h . '");' . "\n";
			$i ++;
		}
		
		echo '
         $(".date-picker").datepicker({
            dateFormat: "dd/mm/yy",
            regional: "es",
            changeMonth: true,
            changeYear: true
          }); 
        
    });   ';
		?>
</script>
<div id="backfooter" class="medopacity"></div>
<div id="footer_bar">
<table width="100%">
	<tr>
		<td width="40"><label style="cursor: pointer;"
			onclick="ancoragoh.client.fadeinout('start_menu');"
			title="Mostrar/Ocultar el men&uacute; principal"> <img
			id="start_button" class="footer-bar-icon" src="images/ancoragoh.gif">
		</label></td>
		<td>
		<div id="footer"></div>
		</td>
		<td align="right">
		<table>
			<tr>
    <?php
				foreach ( $windows as $win )
					echo '<td><img id = "shortcut_' . $win ['id'] . '" class = "footer-bar-icon pointer hidden" src="images/' . $win ['ico'] . '" onclick = "ancoragoh.client.showOnTopWindow(\'' . $win ['id'] . '\');"></td>';
				?>
    
    </tr>
		</table>
		</td>
		<td align="right"><a class="iconlink opaco" href="index.php?logout"><img
			class="iconlink" src="images/arrow_turn_right.png" title="salir"></a>
		</td>
	</tr>
</table>
</div>
<label style="position: absolute; top: 5px; right: 5px; color: white;">&Aacute;ncora
GOH - Generaci&oacute;n y Organizaci&oacute;n de Horarios Docentes</label>
<div id="start_menu">
    <?php
				
				$menu_items = array (array ("t" => "Explorador de horarios", "i" => "horarios.png", "p" => "ver_horarios", "c" => "ancoragoh.client.browsers.showExploradorDeHorarios();", "h" => "Ver los horarios de las brigadas, profesores, lugares y otros recursos" ), array ("t" => "Configuraci&oacute;n del tiempo", "i" => "planificacion.png", "p" => "gestionar_planificaciones", "c" => "ancoragoh.client.browsers.showPlanificaciones();", "h" => "Gestionar las planificaciones" ), array ("t" => "Organizaci&oacute;n de los estudiantes", "i" => "brigada.png", "p" => "gestionar_brigadas", "c" => "ancoragoh.client.browsers.showOrganizacion();", "h" => "Gestionar brigadas" ), array ("t" => "Configuraci&oacute;n de actividades", "i" => "desglose.png", "p" => "gestionar_periodos", "c" => "ancoragoh.client.browsers.desgloses.show();", "h" => "Gestionar los desgloses de actividades y asignar recursos" ), array ("t" => "Recursos", "i" => "otros_recursos.png", "p" => "gestionar_recursos", "c" => "ancoragoh.client.browsers.recursos.show();", "h" => "Gestionar recursos gen&eacute;ricos" ), array ("t" => "Generar horarios", "i" => "generador.png", "p" => "generador", "c" => "ancoragoh.client.wins.generador.show();", "h" => "Gestionar recursos gen&eacute;ricos" ) );
				
				echo "<table>";
				$i = 0;
				foreach ( $menu_items as $m ) {
					if (isset ( $permisos [$m ['p']] ) || isset ( $permisos ['todos'] )) {
						$i ++;
						if ($i == 1)
							echo "<tr>";
						echo '<td valign = "center" class = "start_menu_option">';
						$s = '<label style= "cursor: pointer;" onclick = "' . $m ['c'] . '; $(\'#start_menu\').fadeOut(\'slow\');">';
						echo '<table><tr><td>' . $s . '<img src="images/' . $m ['i'] . '" title="' . $m ['h'] . '" ></label></td><td>' . $s . $m ['t'] . '</label></td></tr></table>';
						echo '</td>';
						if ($i == 2) {
							echo "</tr>";
							$i = 0;
						}
					}
				}
				if ($i == 1)
					echo "<td></td></tr>";
				echo "</table>";
				?>
</div>
<!-- Hidden divs !-->
<div id="dataform"></div>
<div id="imessage"
	class="instead-message ui-widget-content ui-corner-all"></div>

<!-- Ventanas  -->
    <?php
				
				foreach ( $windows as $win ) {
					$w = $win ['id'];
					$r = '';
					if (isset ( $win ['resizable'] ))
						if ($win ['resizable'] == true)
							$r = 'resizable';
					if (isset ( $win ['modal'] ))
						if ($win ['modal'] == true)
							$r .= "ventana-modal";
					?>

<div id="win_<?php
					echo $w;
					?>"
	class="ventana <?php
					echo $r?> hidden"
	onclick="ancoragoh.client.showOnTopWindow('<?php
					echo $w;
					?>');">
<div class="win-buttons">
<table>
	<tr>
							<?php
					if ($win ['minimizable'] == true) {
						?>
                            <td valign="bottom"><img
			src='images/arrow_down.png'
			onclick="ancoragoh.client.minimized('<?php
						echo $w;
						?>');"
			title="Minimizar la ventana"></td>
							<?php
					}
					?>
							<?php
					if ($win ['maximizable'] == true) {
						?>
                            <td valign="bottom"><img
			src='images/arrow_inout.png'
			onclick="ancoragoh.client.maxrestore('<?php
						echo $w;
						?>');"
			title="Maximizar/Restablecer la ventana"></td>
							<?php
					}
					?>
							<?php
					if ($win ['closeable'] == true) {
						?>
                            <td valign="bottom"><img
			src='images/arrow_turn_right.png'
			onclick="ancoragoh.client.hidewin('<?php
						echo $w;
						?>');"
			title="Cerrar la ventana"></td>
							<?php
					}
					?>
                        </tr>
</table>
</div>
<h2 id="win_title_<?php
					echo $w;
					?>" class="form-title"><img class="form-icon"
	src="images/<?php
					echo $win ['ico'];
					?>">
                    <?php
					echo $win ['t'];
					?>
                </h2>
<div id="form_<?php
					echo $w;
					?>" class="win-content"><!-- contenido --></div>
</div>

        <?php
				}
				?>
    <!--  <img id="background" src= "images/wallpapers/background10.jpg"> -->