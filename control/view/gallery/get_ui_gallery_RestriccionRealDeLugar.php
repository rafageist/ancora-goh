<?php 
 /** 
   * @name: gallery views of RestriccionRealDeLugar 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.RestriccionRealDeLugarModel.php");
 import ("model.data.RestriccionRealDeLugar.php");
 import ("model.data.RestriccionRealDeLugarCollection.php");

 $collection = new RestriccionRealDeLugar($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_RestriccionRealDeLugar.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_RestriccionRealDeLugar.tpl";
     break;
   }
 }
?> 
