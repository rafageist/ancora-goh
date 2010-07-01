<?php 
 /** 
   * @name: Combobox views of ClasificacionDeActividad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.ClasificacionDeActividadModel");
 import ("model.data.ClasificacionDeActividad");
 import ("model.data.ClasificacionDeActividadCollection");

 $collection = new ClasificacionDeActividadCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options[$item->id] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_ClasificacionDeActividad.tpl";
       break;
   }
 }
?> 
