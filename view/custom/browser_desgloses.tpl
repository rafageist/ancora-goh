<style>
        li{
            list-style-type: none;
            list-style-image: none;
            cursor: pointer;
        }  
        #treeDesglosesAsig{
        	width: 300px;
        	height: 500px;
        	overflow: auto;
        }
        #desglosesSelectPlanificacion{
        	width: 400px;
        	height: 100px;
        	overflow: auto;
        }
        #desglosesDetalles{
        	width: 400px;
        	height: 300px;
        	overflow: auto;
        }
    </style>


<table width = "100%" height = "100%">
<tr>
		<td colspan = 3>
			<div class = "toolbar">
			<table>
				<tr>
					<td><label id = "btnShowAsignaturas" class = "hidden" onclick = "ancoragoh.client.browsers.desgloses.showLeft();" title = "Asignaturas..."><img src = "images/flechita_izquierda.png"></label></td>
					<td><label id = "btnShowRecursos" onclick = "ancoragoh.client.browsers.desgloses.showRight();" title = "Recursos..."><img src = "images/flechita_derecha.png"></label></td>
					<td><label id = "btnShowAsignaturas" onclick = "ancoragoh.client.browsers.desgloses.refrescarAsignaturas();" title = "Asignaturas"><img src = "images/asignatura.png"></label></td>
					<td><label id = "btnShowClasificaciones" onclick = "ancoragoh.client.browsers.desgloses.refrescarClasifAct();" title = "Clasificaciones de actividades"><img src = "images/clasif_act.png"></label></td>
					<td><label id = "btnShowClasifAct" onclick = "ancoragoh.client.forms.clasifact.showForNew();" title = "Nueva clasificaci&oacute;n de actividad"><img src = "images/clasif_act_add.png"></label></td>
				</tr>
			 </table>
			 </div>
		 </td>
	</tr>
<tr>
	<td valign = "top" style="border-right: 1px dashed black;"> 
		
    <div id = "treeDesglosesAsig">
		<?php include "../view/custom/minibrowser_asignaturas.tpl"; ?>
	</div></td>
	<td>
		<table>
			<tr height = "50">
				<td valign = "top" style="border-bottom: 1px dashed black;">
					<div id = "desglosesSelectPlanificacion">
					<?php 
						include "../view/custom/minibrowser_desgloses_planificaciones.tpl"; 
					?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div id="desglosesDetalles" style="padding: 5px;" ></div>
				</td>
			</tr>
		</table>
	</td>
	<td valign = "top" valign="top" style="border-left: 1px dashed black;"> 
		<div id = "div_recursos" class ="hidden">
		</div>
	</td>
</tr>
<tr>
	
	
	
</tr>
</table>