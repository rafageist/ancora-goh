<?php 
 /** 
   * @name: gallery views of RestriccionRealDeEspecialidad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.RestriccionRealDeEspecialidadModel.php");
 import ("model.data.RestriccionRealDeEspecialidad.php");
 import ("model.data.RestriccionRealDeEspecialidadCollection.php");

 $collection = new RestriccionRealDeEspecialidad($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_RestriccionRealDeEspecialidad.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_RestriccionRealDeEspecialidad.tpl";
     break;
   }
 }
?> 
