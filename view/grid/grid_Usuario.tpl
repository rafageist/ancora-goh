<?php 
 /** 
   * @name: gallery grid of Usuario 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-usuario">
    <tr class = "table-gallery-grid-header table-borwser-grid-usuario">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nombre"><label class = "title-of title-of-nombre">Nombre</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-info"><label class = "title-of title-of-info">Info</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-correo"><label class = "title-of title-of-correo">Correo</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-momento-ingreso"><label class = "title-of title-of-momento-ingreso">Momento ingreso</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-nick"><label class = "title-of title-of-nick">Nick</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-contrasena"><label class = "title-of title-of-contrasena">Contrasena</label></td>
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
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-info">{$item->info}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-correo">{$item->correo}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-momento-ingreso">{$item->momento_ingreso}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-nick">{$item->nick}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-contrasena">{$item->contrasena}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>