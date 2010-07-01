<?php

function putDialogConfirmDelete($title = "Eliminar", $icon = "images/delete.png", 
                                $prompt = "Est&aacute; seguro que desea eliminar", $textbuttoneliminar = "Eliminar", 
                                $functionname = "eliminar", $iddialog = "dialog", $idbtnelminar = "btnEliminar",
                                $idform = "form"){
 echo "
 <script type=\"text/javascript\">
	function $functionname(){
		$(\"#$iddialog\").dialog({
			bgiframe: true,
			resizable: false,
			height:240,
			modal: true,
			overlay: {
				backgroundColor: '#000',
				opacity: 0.5
			},
			buttons: {
				'$textbuttoneliminar': function() {
                    document.getElementById(\"$idbtnelminar\").value = \"yes\";
					$(this).dialog('close');
                    document.getElementById(\"$idform\").submit();
				},
				\"Cancelar\": function() {
					$(this).dialog('close');
				}
			}
		});
        $(\"#$iddialog\").dialog(\"open\");
	}
	</script>
    <div id=\"$iddialog\" style=\"display: none;\" title=\"$title\">
        <p align ='center'><img src='$icon'><br>$prompt</p>
    </div>";
}

?>