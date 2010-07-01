<?php 
 /** 
   * @name: gallery grid of Traza 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-traza">
    <tr class = "table-gallery-grid-header table-borwser-grid-traza">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-fecha"><label class = "title-of title-of-fecha">Fecha</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-usuario"><label class = "title-of title-of-usuario">Usuario</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-accion"><label class = "title-of title-of-accion">Acci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-ip"><label class = "title-of title-of-ip">Ip</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-fecha">{$item->fecha}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-usuario">{$item->usuario}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-accion">{$item->accion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-ip">{$item->ip}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>