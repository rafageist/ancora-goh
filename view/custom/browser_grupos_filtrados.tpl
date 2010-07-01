<label style="cursor: pointer;" onclick="ancoragoh.client.forms.grupo.showForNew();" > 
    <img src="images/grupo_add.png" title="Nuevo grupo">
</label>
<style type="text/css">
    #brgs_sin_grupo { float: left; width: 400px; height: 100px; border: 1px dashed blue; }
    .brgs_sin_grupo.custom-state-active { background: #eee; }
    .brgs_sin_grupo li { float: left; width: 96px; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
    .brgs_sin_grupo li h5 { margin: 0 0 0.4em; cursor: move; }
    .brgs_sin_grupo li a { float: right; }
    .brgs_sin_grupo li a.ui-icon-zoomin { float: left; }
    .brgs_sin_grupo li img {cursor: move; }

    .brgs_con_grupo { width: 400px; height:auto; min-height: 50px; padding: 1%; border: 2px dashed #CCCCCC; }
    .brgs_con_grupo h4 { line-height: 16px; margin: 0 0 0.4em; }
    .brgs_con_grupo h4 .ui-icon { float: left; }
</style>
<script type="text/javascript">
$(function() {
    var $brgs_sin_grupo = $('#brgs_sin_grupo');

    $('#brgs_sin_grupo li, .brgs_con_grupo li').draggable({
        cancel: 'a.ui-icon',
        revert: 'invalid', 
        containment: $('#demo-frame').length ? '#demo-frame' : 'document', 
        helper: 'clone',
        cursor: 'move'
    });
    
    $(".brgs_con_grupo").droppable({
        accept: '#brgs_sin_grupo > li, .brgs_con_grupo li',
        activeClass: 'ui-state-highlight',
        drop: function(ev, ui) {
            var $item = ui.draggable;
            var $este = $("#" + this.id);
            var $grupoid = $("#" + this.id + "_idgrupo");
            $item.find('a.ui-icon-trash').remove();
            $item.fadeOut(function() {
                var $list = $('ul',$este).length ? $('ul',$este) : $('<ul class="brgs_sin_grupo ui-helper-reset"/>').appendTo($este);
                $item.appendTo($list).fadeIn(function() {
                    ancoragoh.server.grupos.moverBrigadaDeGrupo($item.find('input.id').val(), $grupoid.val());
                });
            });                    
        }
    });

    $brgs_sin_grupo.droppable({
        accept: '.brgs_con_grupo li',
        activeClass: 'custom-state-active',
        drop: function(ev, ui) {
            var $item = ui.draggable;
            $item.appendTo($brgs_sin_grupo).fadeIn();
            
			var idesp   = ancoragoh.client.browsers.organizacion.flag_especialidad;
            var idclasif = ancoragoh.client.browsers.organizacion.flag_clasif;
            var idnivel  = ancoragoh.client.browsers.organizacion.flag_nivel;
            var idplanif = ancoragoh.client.browsers.organizacion.flag_planif;
			var idbrg = $item.find('input.id').val();
            ancoragoh.server.grupos.removeBrigadaDeGrupo(idbrg, idesp, idnivel, idclasif, idplanif) ;
			
        }
    });
   
});
</script>
<?php
    $i = 0;
    if (is_array($grupos))
        foreach ($grupos as $gp){
            $i++;
            echo '<input type = "hidden" id ="grupo'.$i.'_idgrupo" value ="'.$gp['id'].'">';
        }
?>
<table>
<?php
    $i = 0;
    if (is_array($grupos)){
        foreach ($grupos as $gp){
            $i++; 
            echo "<tr>";
            $collection = new GrupoCollection($dbconnection);
            
            $grupo = $collection->getItem($gp['id']);
            
            echo '<td>
                <table>
                    <tr>
                        <td>
                          <label style="cursor: pointer" onclick="ancoragoh.client.forms.grupo.showForEdit(\''.$gp['id'].'\')"><img src = "images/grupo.png"> '.$grupo->descripcion."</label>
                        </td>
                        <td>
                        ".'<div id = "grupo'.$i.'" class="brgs_con_grupo ui-widget-content ui-state-default">
                            <ul class="brgs_sin_grupo ui-helper-reset">';
                                
                                $brgs = $grupo->getBrigadaDeGrupo();
                                
                                if (is_array($brgs->collection))
                                    foreach($brgs->collection as $b){
                                        $brg = $brgmodel->get($b->brigada);
                                        echo '<li class = "ui-draggable"><input type="hidden" class="id" value ="'.$brg->id.'"><img src="images/brigada_lite.png" alt="'.$brg->descripcion.'" /><br>'.$brg->descripcion.'</li>';
                                        }
                                echo '
                            </ul>
                        </div>
                        
                        </td>
                    </tr>
                 </table>
                 </td></tr>';
       }
     } else {
        echo "No existen grupos para esta clasificaci&oacute;n";
     }
?>
</table>
<ul id="brgs_sin_grupo" class="brgs_sin_grupo ui-helper-reset ui-helper-clearfix">
<?php 
    if (is_array($brgs_sin_grupo))
        foreach ($brgs_sin_grupo as $brg){
            $idbrg = $brg['dame_brigadas_sin_grupo'];
            $brgobj = $brgmodel->get($idbrg);
            $descrip = $brgobj->descripcion;
            echo '
                    <li>
                        <input type="hidden" class="id" value ="'.$idbrg.'">
                        <img src="images/brigada_lite.png" alt="'.$descrip.'" />
                        <br>
                        '.$descrip.'
                    </li>';
        }
?>
</ul>
