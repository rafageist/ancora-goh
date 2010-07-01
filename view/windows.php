<?php

function getHTMLWindow($win, $onclick){
	$html = '';
	
	$w = $win['id'];
	$r = '';
    if (isset($win['resizable'])) if ($win['resizable']==true) $r = 'resizable';
    if (isset($win['modal'])) if ($win['modal'] == true) $r .= "ventana-modal";
    
    $html .= '<div id = "win_'.$w.'" class = "'.$r.' hidden ventana" onclick ="ancoragoh.client.showOnTopWindow(\''.$w.'\');" style = "position: absolute;">
          <div class = "win-buttons"><table><tr>';
    
    if ($win['minimizable'] == true) {
       $html .= '<td valign = "bottom">
               	<img src = "images/arrow_down.png" onclick ="ancoragoh.client.minimized(\''.$w.'\');" title = "Minimizar la ventana">
                            </td>';
    }
    
	if ($win['maximizable']==true) {
       $html .= '<td valign = "bottom">
           		<img src = "images/arrow_inout.png" onclick = "ancoragoh.client.maxrestore(\''.$w.'\');" title = "Maximizar/Restablecer la ventana">
             </td>';
	}
	
	if ($win['closeable']==true) {
       $html .= '<td valign = "bottom">
                <img src = "images/arrow_turn_right.png" onclick ="ancoragoh.client.hidewin(\''.$w.'\');" title = "Cerrar la ventana">
             </td>';
    }
    
    $html .= '</tr></table></div>
          <h2 id = "win_title_'.$w.'"  class = "form-title">
          <img class="form-icon" src="images/'.$win['ico'].'">
          '.$win['t'].'
          </h2><div id = "form_'.$w.'" class = "win-content">
                    <!-- contenido -->
          </div></div>';
    
   return $html;
}
?>