<?php 
 /** 
   * @name: gallery grid of HerenciaDeRestricciones 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

?>
<table class = "table-gallery-grid table-gallery-grid-herencia-de-restricciones">
    <tr class = "table-gallery-grid-header table-borwser-grid-herencia-de-restricciones">
        <td></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-planificacion"><label class = "title-of title-of-planificacion">Planificaci&oacute;n</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-activa"><label class = "title-of title-of-activa">Activa</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hijo-brigada"><label class = "title-of title-of-hijo-brigada">Hijo brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hijo-profesor"><label class = "title-of title-of-hijo-profesor">Hijo profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hijo-lugar"><label class = "title-of title-of-hijo-lugar">Hijo lugar</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-hijo-otro-recurso"><label class = "title-of title-of-hijo-otro-recurso">Hijo otro recurso</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-padre-brigada"><label class = "title-of title-of-padre-brigada">Padre brigada</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-padre-profesor"><label class = "title-of title-of-padre-profesor">Padre profesor</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-padre-lugar"><label class = "title-of title-of-padre-lugar">Padre lugar</label></td>
        <td class ="table-gallery-grid-header-td table-gallery-header-td-padre-otro-recurso"><label class = "title-of title-of-padre-otro-recurso">Padre otro recurso</label></td>
        <td><button>Eliminar</button></td>
        <td></td>
    </tr>

<?php 
 $i = $offset;
 if (is_array($collection->collection )) 
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-planificacion">{$item->planificacion}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-activa">{$item->activa}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hijo-brigada">{$item->hijo_brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hijo-profesor">{$item->hijo_profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hijo-lugar">{$item->hijo_lugar}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-hijo-otro-recurso">{$item->hijo_otro_recurso}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-padre-brigada">{$item->padre_brigada}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-padre-profesor">{$item->padre_profesor}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-padre-lugar">{$item->padre_lugar}</td>';
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-padre-otro-recurso">{$item->padre_otro_recurso}</td>';
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>