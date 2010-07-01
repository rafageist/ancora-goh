<?php 

   $cc = 0;
   
   echo '<div id = "browserPlanficaciones">';
?>

<!-- Delete confirmation frame -->

<input type = "hidden" id = "minifield_Planificacion_Id" value = "">

<div id = "frame_browser_del_Planificacion" class = "frame-delete">
	<table>
		<tr>
			<td><img src = "images/planificacion_delete.png"></td>
			<td>
				Est&aacute; seguro que desea eliminar?<br>
				<input type = "button" onclick = "ancoragoh.client.browsers.planificaciones.eliminar();" value = "Eliminar"> 
				<input type = "button" onclick = "$('#frame_browser_del_Planificacion').fadeOut('slow');" value = "Cancelar">
			</td>
		</tr>
	</table>
</div>

<?php 
   echo "<table>";
     if (is_array($planifs->collection))
          foreach ($planifs->collection  as $planif){
          	$cc++;
          	if ($cc == 1) echo "<tr>";
          	
			echo "
			<td align='center'>
			    <table class = 'gallery-icon gallery-icon-planificacion'>
			        <tr>
			            <td class = \"gallery-subicon\">
			            	
			                <label style = 'width: 100px;' class = \"iconlink\" onclick =\"ancoragoh.client.forms.planificacion.showForEdit('{$planif->id}');\"> 
			                  <img src='images/planificacion.png'><br>
			                  {$planif->descripcion}
			                </label>
			            </td>
			            <td>
			                <table>
			                    <tr>
			                        <td class =\"mini-icon\">
			                            <label class = \"iconlink \" onclick =\"ancoragoh.client.forms.periodo.showForNew('{$planif->id}');\" title = 'Agregar un per&iacute;odo a esta planificaci&oacute;n'>
			                                <img src='images/periodo_add_lite.png'>
			                            </label>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td class =\"mini-icon\">
			                            <label class = \"iconlink\" onclick =\"ancoragoh.client.browsers.planificaciones.showPeriodos('{$planif->id}');\" title = 'Per&iacute;odos de esta planificaci&oacute;n'>
			                                <img src='images/periodo_lite.png'>
			                            </label>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td class =\"mini-icon\">
			                            <label class = \"iconlink\" onclick =\"ancoragoh.client.browsers.planificaciones.showEliminar('{$planif->id}');\" title = 'Eliminar esta planificaci&oacute;n'>
			                                <img src='images/delete_lite.png'>
			                            </label>
			                        </td>
			                    </tr>
			                </table>
			            </td>
			         </tr>
			    </table>
			</td>";
         if ($cc == 3) {
         		echo '</tr>';
         		$cc = 0;	         
         	}
         }
         
      if ($cc < 3) echo "</tr>";   
echo "</table></div>";
?>