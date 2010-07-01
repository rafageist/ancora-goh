<?php 
 /** 
   * @name: gallery views of Traza 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.TrazaModel.php");
 import ("model.data.Traza.php");
 import ("model.data.TrazaCollection.php");

 $collection = new Traza($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Traza.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Traza.tpl";
     break;
   }
 }
?> 
