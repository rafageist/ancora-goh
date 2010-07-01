<?php 
 /** 
   * @name: gallery views of RestriccionCondicional 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.RestriccionCondicionalModel.php");
 import ("model.data.RestriccionCondicional.php");
 import ("model.data.RestriccionCondicionalCollection.php");

 $collection = new RestriccionCondicional($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_RestriccionCondicional.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_RestriccionCondicional.tpl";
     break;
   }
 }
?> 
