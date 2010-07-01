<script>
        function showhide(id){
            if ($('#' + id).css('display')=='block') 
                $('#' + id).css('display','none'); 
            else
                $('#' + id).css('display','block'); 
        }
</script>
<style>
        li{
            list-style-type: none;
            list-style-image: none;
            cursor: pointer;
        }
    </style>
<?php

    $es = $especs->getAll();
    $ns = $niveles->getAll();
    
    $i = 0;
    echo '<ul>';
    foreach ($es as $esp){
        $i++;
        $id = "ul_esp_$i";
        echo "<li><label style = 'cursor: pointer;' onclick = \"showhide('$id');\"><img src='images/especialidad_lite.png'>".$esp['descripcion']."</label>";
        echo "<ul id='$id'>";
        $j=0;
        foreach ($ns as $nivel){
        	$j++;
        	$id .= "_".$j;
        	echo "<li><label style = 'cursor: pointer;' onclick = \"showhide('$id');\"><img src='images/nivel_lite.png'>".$nivel['descripcion']."</label>";
            
            $bs = $brgs->getAllBy("especialidad ='{$esp['id']}' AND nivel = '{$nivel['id']}'");
            
            if (is_array($bs)){
            	echo "<ul id='$id'>";
                foreach ($bs as $brg)
            		echo "<li><label style = 'cursor: pointer;' onclick = 'ancoragoh.client.browsers.publico.showHorario(\"".$brg['id']."\")'> <img src='images/brigada_lite.png'>".$brg['descripcion']."</label>";	
            	echo "</ul>";
            }
            
            echo "</li>";
        }
        echo "</ul></li>";
        
    }
    echo "</ul>";
?>