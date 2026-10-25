`<!--{ Grid view of one table (view/grid/grid_<Class>.tpl): one column per
     field that is not a replica key. }-->`<?php`{\s}`
 /**`{\s}`
   * @name: gallery grid of `{$classname}``{\s}`
   * @desc: Part View of MVC pattern`{\s}`
   * @updated: `{$updated}``{\s}`
   * @version: 1.0`{\s}`
   */`{\s}`

?>
<table class = "table-gallery-grid table-gallery-grid-`{$css}`">
    <tr class = "table-gallery-grid-header table-borwser-grid-`{$css}`">
        <td></td>`[$columns]`
        <td class ="table-gallery-grid-header-td table-gallery-header-td-`{$css}`"><label class = "title-of title-of-`{$css}`">`{$title}`</label></td>`[/$columns]`
        <td><button>`{$deleteButton}`</button></td>
        <td></td>
    </tr>

<?php`{\s}`
 $i = $offset;
 if (is_array($collection->collection ))`{\s}`
    foreach ($collection->collection as $item){
       $i++;
       echo "<tr class = \"table-gallery-grid-row\">";`[$columns]`
       echo '   <td class ="table-gallery-grid-td table-gallery-grid-td-`{$css}`">{$item->`{$name}`}</td>';`[/$columns]`
       echo "   <td><input type='checkbox' name='chk_$i'></td><td><input type='submit' name='edtEdit_$i'></td>";
       echo "</tr>";
    }
?>
</table>
