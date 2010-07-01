<?php 
 /** 
   * @name: Combobox views of ExcepcionDeHrt 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.ExcepcionDeHrtModel");
 import ("model.data.ExcepcionDeHrt");
 import ("model.data.ExcepcionDeHrtCollection");

 $collection = new ExcepcionDeHrtCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->herencia.','.$item->periodo.','.$item->dia.','.$item->turno.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_ExcepcionDeHrt.tpl";
       break;
   }
 }
?> 
