<?php 
 /** 
   * @name: gallery views of PlantillaDeActividad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.PlantillaDeActividadModel.php");
 import ("model.data.PlantillaDeActividad.php");
 import ("model.data.PlantillaDeActividadCollection.php");

 $collection = new PlantillaDeActividad($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_PlantillaDeActividad.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_PlantillaDeActividad.tpl";
     break;
   }
 }
?> 
