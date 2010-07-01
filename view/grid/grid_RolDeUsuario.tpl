<?php 
 /** 
   * @name: gallery grid of RolDeUsuario 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-rol-de-usuario">
    <tr class = "table-gallery-grid-header table-borwser-grid-rol-de-usuario">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-usuario"><label class = "title-of title-of-usuario">Usuario</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-rol"><label class = "title-of title-of-rol">Rol</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-usuario">{$item->usuario}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-rol">{$item->rol}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>