<?php 
 /** 
   * @name: Combobox views of Traza 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.TrazaModel");
 import ("model.data.Traza");
 import ("model.data.TrazaCollection");

 $collection = new TrazaCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 $i = 0;
 foreach($collection->collection as $item){
 $i++;
     $options[$i] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_Traza.tpl";
       break;
   }
 }
?> 
