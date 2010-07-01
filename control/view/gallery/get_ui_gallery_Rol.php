<?php 
 /** 
   * @name: gallery views of Rol 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:16 
   * @version: 1.0 
   */ 

 import ("model.data.RolModel.php");
 import ("model.data.Rol.php");
 import ("model.data.RolCollection.php");

 $collection = new Rol($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Rol.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Rol.tpl";
     break;
   }
 }
?> 
