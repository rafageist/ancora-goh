<?php 
 /** 
   * @name: Combobox views of Zpriori 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 import ("model.data.ZprioriModel");
 import ("model.data.Zpriori");
 import ("model.data.ZprioriCollection");

 $collection = new ZprioriCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->clasificacion.','.$item->periodo.','.$item->dia.','.$item->turno.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_Zpriori.tpl";
       break;
   }
 }
?> 
