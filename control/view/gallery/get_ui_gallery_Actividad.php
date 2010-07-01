<?php 
 /** 
   * @name: gallery views of Actividad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.ActividadModel.php");
 import ("model.data.Actividad.php");
 import ("model.data.ActividadCollection.php");

 $collection = new Actividad($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_Actividad.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_Actividad.tpl";
     break;
   }
 }
?> 
