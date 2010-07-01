<?php 
 /** 
   * @name: gallery gallery of RestriccionRealDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

if (!isset($link)) $link = "";
if (!isset($fieldlink)) $fieldlink = "";
if (!isset($columns)) $columns = 3;

echo $title;
echo '<table class = "view-gallery" id = "view-gallery-restriccion_real_de_brigada"><tr>';
$i = $offset; $c=0;
if (is_array($collection->collection )) foreach ($collection->collection as $item){
   $i++; $c++;
   if ($i == $offset+1 || $c==1) echo "<tr>";
   if ($c>$columns) {echo "</tr><tr>"; $c = 1;}
   $link_ini =""; $link_fin = ""; 
   if ($link!="")
      eval('$link_ini = "<a href = \'$link&$fieldlink=".$item->'.$fieldlink.'."\'>";'); 
   echo "<td align='center'><div class=\"gallery\">$link_ini<img src='$icon'><br>";
   eval('echo $item->'.$fielddescrip.";");
   echo "$link_fin</div></td>";
}
echo "</table>";


?> 
