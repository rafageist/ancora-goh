<?php 
 /** 
   * @name: gallery grid of Planificacion 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-planificacion">
    <tr class = "table-gallery-grid-header table-borwser-grid-planificacion">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-descripcion"><label class = "title-of title-of-descripcion">Descripci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-fecha-inicial"><label class = "title-of title-of-fecha-inicial">Fecha inicial</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-cant-dias"><label class = "title-of title-of-cant-dias">Cant dias</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-activa"><label class = "title-of title-of-activa">Activa</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-creador"><label class = "title-of title-of-creador">Creador</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-fecha-inicial">{$item->fecha_inicial}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-cant-dias">{$item->cant_dias}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-activa">{$item->activa}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>