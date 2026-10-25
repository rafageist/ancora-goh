`<!--{ Gallery view of one table (view/gallery/gallery_<Class>.tpl). The
     link and description fields must name a property of the item: no eval
     of request text (#14). }-->`<?php`{\s}`
 /**`{\s}`
   * @name: gallery gallery of `{$classname}``{\s}`
   * @desc: Part View of MVC pattern`{\s}`
   * @updated: `{$updated}``{\s}`
   * @version: 1.0`{\s}`
   */`{\s}`

if (!isset($link)) $link = "";
if (!isset($fieldlink)) $fieldlink = "";
if (!isset($columns)) $columns = 3;

echo $title;
echo '<table class = "view-gallery" id = "view-gallery-`{$name}`"><tr>';
$i = $offset; $c=0;
if (is_array($collection->collection )) foreach ($collection->collection as $item){
   $i++; $c++;
   if ($i == $offset+1 || $c==1) echo "<tr>";
   if ($c>$columns) {echo "</tr><tr>"; $c = 1;}
   $link_ini =""; $link_fin = "";`{\s}`
   if ($link!="")
      // #14: no eval of request text; $fieldlink must name a property of $item
      if (is_string($fieldlink) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $fieldlink) && is_object($item) && property_exists($item, $fieldlink))
         $link_ini = "<a href = '".htmlspecialchars($link."&".$fieldlink."=".$item->$fieldlink, ENT_QUOTES)."'>";
   echo "<td align='center'><div class=\"gallery\">$link_ini<img src='$icon'><br>";
   if (isset($fielddescrip) && is_string($fielddescrip) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $fielddescrip) && is_object($item) && property_exists($item, $fielddescrip))
      echo $item->$fielddescrip;
   echo "$link_fin</div></td>";
}
echo "</table>";


?>`{\s}`

