<?php 
 /** 
   * @name: gallery views of Brigada 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.BrigadaModel.php");
 import ("model.data.Brigada.php");
 import ("model.data.BrigadaCollection.php");

 $collection = new Brigada($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Brigada.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Brigada.tpl";
     break;
   }
 }
?> 
