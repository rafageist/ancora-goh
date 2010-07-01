<?php 
 /** 
   * @name: gallery views of RestriccionRealDeProfesor 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.RestriccionRealDeProfesorModel.php");
 import ("model.data.RestriccionRealDeProfesor.php");
 import ("model.data.RestriccionRealDeProfesorCollection.php");

 $collection = new RestriccionRealDeProfesor($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_RestriccionRealDeProfesor.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_RestriccionRealDeProfesor.tpl";
     break;
   }
 }
?> 
