<?php 
 /** 
   * @name: gallery grid of Rol 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-rol">
    <tr class = "table-gallery-grid-header table-borwser-grid-rol">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nombre"><label class = "title-of title-of-nombre">Nombre</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-descripcion"><label class = "title-of title-of-descripcion">Descripci&oacute;n</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nombre">{$item->nombre}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-descripcion">{$item->descripcion}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>