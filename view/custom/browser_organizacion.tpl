<input type = "hidden" id = "data_organizacion_espec" value = "<?php echo $idfirst_espec; ?>">
<input type = "hidden" id = "data_organizacion_nivel" value = "<?php echo $idfirst_nivel; ?>">
<input type = "hidden" id = "data_organizacion_planif" value = "<?php echo $idfirst_planif; ?>">
<input type = "hidden" id = "data_organizacion_clasificacion" value = "">
<table width = "100%" height = "100%">
	<tr height = "150">
		
		<td align = "center" valign = "center" colspan = "3" >
			<div id = "div-organizacion-top">
				<?php include "../view/custom/browser_organizacion_top.tpl"; ?>
			</div>
		</td>
		
	</tr>
	<tr>
		<td  align = "center" valign = "center" width = "100">
			<div id = "div-organizacion-left">
				<?php include "../view/custom/browser_organizacion_left.tpl"; ?>
			</div>
		</td>
		<td align = "center" valign = "center">
			<div id = "div-organizacion-center">
				<?php include "../view/custom/browser_organizacion_center.tpl"; ?>
			</div>
		</td>
		<td valign = "top" align = "center" width = "100">
			<div id = "div-organizacion-right">
				<?php include "../view/custom/browser_organizacion_right.tpl"; ?>
			</div>
		</td>
	</td>
	<tr>
		<td></td>
		<td  align = "center" valign = "center">
			<div id = "div-organizacion-bottom">
				<?php include "../view/custom/browser_organizacion_bottom.tpl"; ?>
			</div>
		</td>
		<td></td>
	</tr>
</table>