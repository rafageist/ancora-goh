<?php 
 /** 
   * @name: gallery views of Especialidad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.EspecialidadModel.php");
 import ("model.data.Especialidad.php");
 import ("model.data.EspecialidadCollection.php");

 $collection = new Especialidad($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Especialidad.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Especialidad.tpl";
     break;
   }
 }
?> 
