<?php 
 /** 
   * @name: Combobox views of Rol 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:16 
   * @version: 1.0 
   */ 

 import ("model.data.RolModel");
 import ("model.data.Rol");
 import ("model.data.RolCollection");

 $collection = new RolCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options[$item->nombre] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_Rol.tpl";
       break;
   }
 }
?> 
