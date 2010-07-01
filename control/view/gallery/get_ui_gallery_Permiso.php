<?php 
 /** 
   * @name: gallery views of Permiso 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.PermisoModel.php");
 import ("model.data.Permiso.php");
 import ("model.data.PermisoCollection.php");

 $collection = new Permiso($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Permiso.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Permiso.tpl";
     break;
   }
 }
?> 
