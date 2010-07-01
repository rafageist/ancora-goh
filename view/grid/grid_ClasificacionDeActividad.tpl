<?php 
 /** 
   * @name: gallery grid of ClasificacionDeActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-clasificacion-de-actividad">
    <tr class = "table-gallery-grid-header table-borwser-grid-clasificacion-de-actividad">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-descripcion"><label class = "title-of title-of-descripcion">Descripci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-duracion"><label class = "title-of title-of-duracion">Duraci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-creador"><label class = "title-of title-of-creador">Creador</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-prioridad"><label class = "title-of title-of-prioridad">Prioridad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-gasto"><label class = "title-of title-of-gasto">Gasto</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-descripcion">{$item->descripcion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-duracion">{$item->duracion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-prioridad">{$item->prioridad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-gasto">{$item->gasto}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>