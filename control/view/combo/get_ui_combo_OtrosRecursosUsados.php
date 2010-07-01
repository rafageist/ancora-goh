<?php 
 /** 
   * @name: Combobox views of OtrosRecursosUsados 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.OtrosRecursosUsadosModel");
 import ("model.data.OtrosRecursosUsados");
 import ("model.data.OtrosRecursosUsadosCollection");

 $collection = new OtrosRecursosUsadosCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->otro_recurso.','.$item->actividad.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_OtrosRecursosUsados.tpl";
       break;
   }
 }
?> 
