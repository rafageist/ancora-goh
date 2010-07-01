<?php 
 /** 
   * @name: Combobox views of Receso 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.RecesoModel");
 import ("model.data.Receso");
 import ("model.data.RecesoCollection");

 $collection = new RecesoCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->periodo.','.$item->despues_del_turno.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_Receso.tpl";
       break;
   }
 }
?> 
