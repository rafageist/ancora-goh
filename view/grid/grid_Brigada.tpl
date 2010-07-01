<?php 
 /** 
   * @name: gallery grid of Brigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-brigada">
    <tr class = "table-gallery-grid-header table-borwser-grid-brigada">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-descripcion"><label class = "title-of title-of-descripcion">Descripci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-especialidad"><label class = "title-of title-of-especialidad">Especialidad</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nivel"><label class = "title-of title-of-nivel">Nivel</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-matricula"><label class = "title-of title-of-matricula">Matricula</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-especialidad">{$item->especialidad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nivel">{$item->nivel}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-matricula">{$item->matricula}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>