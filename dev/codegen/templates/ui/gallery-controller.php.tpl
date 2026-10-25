`<!--{ Controller of the gallery and grid views of one table
     (control/view/gallery/get_ui_gallery_<Class>.php). }-->`<?php`{\s}`
 /**`{\s}`
   * @name: gallery views of `{$classname}``{\s}`
   * @desc: Part Control of MVC pattern`{\s}`
   * @updated: `{$updated}``{\s}`
   * @version: 1.0`{\s}`
   */`{\s}`

 import ("`{$package}`.`{$classname}`Model.php");
 import ("`{$package}`.`{$classname}`.php");
 import ("`{$package}`.`{$classname}`Collection.php");

 $collection = new `{$classname}`(`{$rt.db_global}`);
 $collection->Load();

 if (isset($_POST["forma"])){`{\s}`
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "`{$rt.view_path}`/gallery/gallery_`{$classname}`.tpl";
       break;
     case "grid":
       include "`{$rt.view_path}`/gallery_grid_`{$classname}`.tpl";
     break;
   }
 }
?>`{\s}`

