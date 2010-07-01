<?php 
 /** 
   * @name: gallery views of CostoDeTraslado 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.CostoDeTrasladoModel.php");
 import ("model.data.CostoDeTraslado.php");
 import ("model.data.CostoDeTrasladoCollection.php");

 $collection = new CostoDeTraslado($dbconnection);
 $collection->Load();

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "gallery":
       if (isset($_POST["link"])) $link = $_POST["link"];
       if (isset($_POST["fieldlink"])) $fieldlink = $_POST["link"];
       if (isset($_POST["columns"])) $columns = $_POST["columns"];
       include "../view/gallery/gallery_CostoDeTraslado.tpl";
       break;
     case "grid":
       include "../view/gallery_grid_CostoDeTraslado.tpl";
     break;
   }
 }
?> 
