<?php 
 /** 
   * @name: gallery views of OtrosRecursosUsados 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.OtrosRecursosUsadosModel.php");
 import ("model.data.OtrosRecursosUsados.php");
 import ("model.data.OtrosRecursosUsadosCollection.php");

 $collection = new OtrosRecursosUsados($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_OtrosRecursosUsados.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_OtrosRecursosUsados.tpl";
     break;
   }
 }
?> 
