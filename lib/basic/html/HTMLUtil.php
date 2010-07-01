<?php
/**
 *   Utils for HTML building
 */
 
function getHTMLRoundedDiv($content,$background,$color,$extrastyle='',$colorborder=false){

	$border = $colorborder;
	$idem = "style='display:block;height: 1px; overflow: hidden; background: $color;margin: 0 ";
	$w='width:98%;margin-left:3;';
	if ($colorborder == false) {$border = $background; $w='';}

	$idem = "style='display:block;height: 1px; overflow: hidden; background: $color;margin: 0 ";
    $round=  "<div style='margin:0;background:$color;$extrastyle;$w;'>
                <b style='display:block;background:$border'>
                        <b $idem 5px'></b>
                        <b $idem 3px'></b>
                        <b $idem 2px'></b>
                        <b $idem 1px;height: 2px'></b>
                </b>
                $content
                <b style='display:block;background:$border'>
                        <b $idem 1px;height: 2px'></b>
                        <b $idem 2px'></b>
                        <b $idem 3px'></b>
                        <b $idem 5px'></b>
                    </b>
            </div>";

	if ($colorborder == false) return $round;

	$idem = "style='display:block;height: 1px; overflow: hidden; background:$border;margin: 0 ";

        return "<div style='margin:0;background:$border;text-align:center;$extrastyle;'>
                <b style='display:block;background:$background;'>
                        <b $idem 5px'></b>
                        <b $idem 3px'></b>
                        <b $idem 2px'></b>
                        <b $idem 1px;height: 2px'></b>
                </b>
                $round
                <b style='display:block;background:$background;'>
                        <b $idem 1px;height: 2px'></b>
                        <b $idem 2px'></b>
                        <b $idem 3px'></b>
                        <b $idem 5px'></b>
                    </b>
            </div>";

    }

function getHTMLDeleteConfirm(){
    return "
        <script>
            function confirmaEliminar(){
                var r=confirm('Seguro que desea eliminar?');
                if (r==true){
                    document.frmDatos.submit();
                }
            }
        </script>";
}

function getHTMLAccessDenied(){
        return '<script>setTimeout("document.getElementById(\'access\').style.display=\'none\';",5000);</script>
              <div class="div-error" id="access">ACCESS DENIED</div>';                 
    }

function getHTMLInstantMessage($message){
        return '<script>setTimeout("document.getElementById(\'message\').style.display=\'none\';",5000);</script>
                <div class="instead-message" id="message">'.$message.'</div>';
    }	
	
function getHTMLCenter($content){
	return "<table align='center'><tr><td>$content</td></tr></table>";
}

function getHTMLCombobox($name, $id, $options, $class = "", $default = null, $label = null, $br = true){
    
    if ($label != null){
       echo "<label class = \"label-combo label-combo-$name\">$label</label>";
       if ($br == true) echo "<br>";
    }
    
    $s = "<select name = \"$name\" id = \"$id\" class = \"combobox combobox-$name $class\">";
    
    if (is_array($options))
    	foreach($options as $value => $desc)
        	$s .= "<option value = \"$value\" ".(($default == $value)?"selected":"").">$desc</option>";
    
    $s .= "</select>";
    
    return $s;
}

?>
