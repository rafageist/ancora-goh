<script>
	function refrescaGenerador(){
		ancoragoh.client.wins.generador.refrescaCola();
		ancoragoh.client.wins.generador.refrescaEspecialidades();
		ancoragoh.client.wins.generador.refrescaNiveles();
		//ancoragoh.client.wins.generador.refrescaPlanificaciones();
		ancoragoh.client.wins.generador.refrescaPeriodos();
	}
	$(function(){
		refrescaGenerador();
		
		});
</script>
<input type="button" class="button" value="Cola de generaci&oacute;n"
	onclick="ancoragoh.client.wins.generador.selectCola();">
<input type="button" class="button" value="Estrategia"
	onclick="ancoragoh.client.wins.generador.selectEstrategia();">

<div width="450" id="generador_cola">

<fieldset><legend> Cola de generaci&oacute;n </legend>
<div id="cola_generacion"></div>
</fieldset>

</div>

<div id="generador_estrategia" class="hidden">
<fieldset width="100%"><legend>Estrategia</legend>
<table width="100%">
	<tr>
		<td>

		<table>
			<tr>
				<td>Compactar al inicio del per&iacute;odo</td>
				<td>
				<table>
					<tr>
							<?php
							for($i = 1; $i <= 10; $i ++)
								echo '<td valign = "bottom">
									<div class = "bar_generador_compactar iconlink" onclick = "ancoragoh.client.wins.generador.estrategia.setCompactar(' . $i . ');" id = "generador_compact_' . $i . '" style="background: gray; height: ' . intval ( $i * ($i - $i / 2) ) . 'px; width: 10px;">
									</div>
									</td>';
							?>			
							</tr>
				</table>
			
			</tr>
		</table>
		</td>
		<td>
		<fieldset width="100%"><legend>Priorizar ...</legend>

		<table width="100%">
			<tr>
				<td id="generador_select_priorizar1" align="center" valign="top"
					class="iconlink"
					onclick="ancoragoh.client.wins.generador.estrategia.setPriorizar(1);"><img
					src="images/clasif_act.png"><br>
				... el orden de prioridad de las clasificaciones de actividades</td>
			</tr>
			<tr>
				<td id="generador_select_priorizar2" align="center" valign="top"
					class="iconlink"
					onclick="ancoragoh.client.wins.generador.estrategia.setPriorizar(2);"><img
					src="images/asignatura.png"><br>
				... la cantidad de actividades de las asignaturas</td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
	<tr>
		<td><input type="checkbox" id="generador_select_agrupar"> Agrupar
		actividades por su clasificaci&oacute;n</td>

		<td><input type="checkbox" id="generador_select_maximizar"> Maximizar
		la combinaci&oacute;n de actividades por d&iacute;a</td>
	</tr>
	<tr>
		<td colspan="2">
		<fieldset width="100%"><legend>Seleccionar lugares seg&uacute;n ...</legend>

		<table width="100%">
			<tr>
				<td id="generador_select_lugar1" align="center" valign="top"
					class="iconlink"
					onclick="ancoragoh.client.wins.generador.estrategia.setLugar(1);"><img
					src="images/lugar.png"><br>
				... el m&aacute;s utilizado</td>
				<td id="generador_select_lugar2" align="center" valign="top"
					class="iconlink"
					onclick="ancoragoh.client.wins.generador.estrategia.setLugar(2);"><img
					src="images/distancias.png"><br>
				... el m&aacute;s cercano</td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
</table>
</fieldset>
</div>
<input type="button" class="button" value ="Generar.." onclick = "ancoragoh.client.wins.generador.generar();">
<input type="button" class="button" value ="Detener" onclick = "ancoragoh.client.wins.generador.detener();">

<div id ="generador_emo_working" class ="hidden">
	<img src = "images/emo/generando.gif">
</div>
