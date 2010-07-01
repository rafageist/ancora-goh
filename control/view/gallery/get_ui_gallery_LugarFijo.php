<?php 
 /** 
   * @name: gallery views of LugarFijo 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.LugarFijoModel.php");
 import ("model.data.LugarFijo.php");
 import ("model.data.LugarFijoCollection.php");

 $collection = new LugarFijo($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_LugarFijo.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_LugarFijo.tpl";
     break;
   }
 }
?> 
