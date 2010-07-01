<?php 
 /** 
   * @name: gallery views of CancelarGenerador 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.CancelarGeneradorModel.php");
 import ("model.data.CancelarGenerador.php");
 import ("model.data.CancelarGeneradorCollection.php");

 $collection = new CancelarGenerador($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_CancelarGenerador.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_CancelarGenerador.tpl";
     break;
   }
 }
?> 
