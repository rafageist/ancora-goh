<body>

<?php
$windows [] = array ("t" => "Horarios", "id" => "publico", "ico_lite" => "horarios_lite.png", "ico" => "horarios.png", "draggable" => true, "resizable" => true, "closeable" => true, "minimizable" => false, "maximizable" => true, "modal" => true, "height"=>"400px", "width"=>"700px" );
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

<table id="table-desktop" width="100%" height="100%">
	<!-- <tr height="40px">
        <td>
            <div id="top"><img src="images/ancoragoh16x16.png"> &Aacute;ncora GOH - Generaci&oacute;n y Organizaci&oacute;n de Horarios Docentes</div>
		</td>
	</tr> -->
	<tr>
		<td>

		<form name="formauth" method="post" action="index.php">
		<table align="center">
			<tr>
				<td><img src="images/anc61.gif"></td>
				<td align="right" class="authform"><input name="edtUser"
					id="edtUser" type="text" value="" class="edtUser"><br>
				<br>
				<input name="edtPass" id="edtPass" type="password" value=""
					class="edtPass"><br>
				<br>
				<input class="button" type="submit" value="Entrar" name="btnLogin">
				&nbsp;</td>
			</tr>
		</table>
		</form>

		</td>
	</tr>
</table>

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
<?php if ($this->isSecuritySwitchOn ( 'public_viewer_anonymous' )) { ?>
<label style="position: absolute; bottom: 10px; right: 10px"
	class="iconlink opaco"
	onclick="ancoragoh.client.browsers.publico.show();" title="horarios"> <img
	src="images/horarios.png"></label>
<?php } ?>