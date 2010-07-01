<?php 
 /** 
   * @name: gallery grid of Lugar 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-lugar">
    <tr class = "table-gallery-grid-header table-borwser-grid-lugar">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nombre"><label class = "title-of title-of-nombre">Nombre</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-capacidad"><label class = "title-of title-of-capacidad">Capacidad</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nombre">{$item->nombre}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-capacidad">{$item->capacidad}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-creador">{$item->creador}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>