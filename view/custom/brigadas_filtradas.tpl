
<?php
if (is_array($arr)) {
	foreach ($arr as $brg){
		            
		echo "<div class = 'itemicon'>
				<label class = 'iconlink' onclick=\"ancoragoh.client.forms.brigada.showForEdit('".$brg['id']."')\">
				<img src='images/brigada.png'> <br> ".$brg['descripcion'].
				"</label><br>".
				($brg['matricula']==0?'':$brg['matricula'].($brg['matricula']>1?' estudiantes':' estudiante')).'
			  </div>';
	}
	
}
?>
<div class = 'itemicon '>
<img class = "iconlink" src = "images/brigada_add.png" title = "Nueva brigada" onclick = "ancoragoh.client.forms.brigada.showForNew();">
</div>
