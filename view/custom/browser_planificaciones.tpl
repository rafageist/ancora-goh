<table width = "100%">
	<tr>
		<td colspan = 2>
			<div class = "toolbar">
			<table>
				<tr>
					<td><img class = "iconlink" src = "images/arrow_refresh_small.png" title = "Refrescar" onclick = "ancoragoh.client.browsers.planificaciones.refrescar();"></td>
					<td><img class = "iconlink" src = "images/planificacion_add.png" title = "Nueva planificaci&oacute;n" onclick = "ancoragoh.client.forms.planificacion.showForNew();"></td>
					<td><img class = "iconlink" src = "images/planificacion.png" title = "Gestionar planificaciones" onclick = "$('#browserPlanficaciones').fadeIn('fast'); $('#div-periodos').fadeIn('fast'); $('#diagrama_gantt').fadeOut('fast');"></td>
					<td><img class = "iconlink" src = "images/gantt.png" title = "Diagrama de gantt" onclick = "$('#diagrama_gantt').fadeIn('fast'); $('#browserPlanficaciones').fadeOut('fast'); $('#div-periodos').fadeOut('fast'); "></td>
				</tr>
			 </table>
			 </div>
		 </td>
	</tr>
	<tr>
		<td valign = "top">
		<?php include "../view/custom/minibrowser_planificaciones.tpl"?>
		<fieldset id = "planificaciones_diagrama_gantt">
			<legend>Diagrama de Gantt de las Planificaciones</legend>
			<?php include "../view/custom/diagrama_gantt_planificaciones.tpl"?>
		</fieldset>
	</td>
	<td valign = "top">
		<div id = "div-periodos" class = "hidden">
		</div>
	</td>
</tr>
</table>