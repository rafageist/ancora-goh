<style>
#divTree {
	width: 200px;
	height: 400px;
	overflow: auto;
	background: #EEEEEE;
}
</style>
<table>
	<tr>
		<td valign="top">

		<table style="border-right: 1px solid black; height: 100%;"
			height="100%">
			<tr>
				<td>
				<div class="toolbar">
				<table>
					<tr>
						<td valign="top"><img src="images/brigada.png" class="iconlink"
							onclick="ancoragoh.client.browsers.showTreeBrgs('divTree');" title="Mostrar horarios de brigadas">
						<td><img
							src="images/profesor.png" class="iconlink"
							onclick="ancoragoh.client.browsers.showExploradorListProfes('divTree');" title="Mostrar horarios de profesores">
							</td>
						</td><td><img src="images/lugar.png" class="iconlink"
							onclick="ancoragoh.client.browsers.showExploradorListLugares('divTree');"  title="Mostrar horarios de lugares">
						</td><td><img src="images/otro_recurso.png" class="iconlink"
							onclick="ancoragoh.client.browsers.showExploradorListOtrosRecursos('divTree');"  title="Mostrar horarios de otros recursos">
						</td>
					</tr>
				</table>
				</div>
			
			</tr>
			<tr>
				<td>
				<div id="divTree"></div>
				</td>
			</tr>
		</table>
		</td>

		<td valign="top">
		<table>
			<tr height="50">
				<td valign="top">Per&iacute;odo: <select id="explorador_cboPeriodo"
					onchange="ancoragoh.client.browsers.horarios.refrescarHorario();">
				<?php
				if (is_array ( $planifs )) {
					foreach ( $planifs as $planif ) {
						echo '<optgroup label ="' . $planif->descripcion . '">';
						if (is_array ( $planif->periodos->collection ))
							foreach ( $planif->periodos->collection as $p ) {
								echo '<option value = "' . $p->id . '">' . $p->descripcion . '</option>';
							}
						echo '</optgroup>';
					}
				}
				?>
				
				</select></td>
			</tr>
			<tr>
				<td valign="top">
				<div id="divExplorerDetail"></div>
				</td>
			</tr>
		</table>


		</td>
	</tr>
</table>