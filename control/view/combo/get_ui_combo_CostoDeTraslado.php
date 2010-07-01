<?php 
 /** 
   * @name: Combobox views of CostoDeTraslado 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 import ("model.data.CostoDeTrasladoModel");
 import ("model.data.CostoDeTraslado");
 import ("model.data.CostoDeTrasladoCollection");

 $collection = new CostoDeTrasladoCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->origen.','.$item->destino.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_CostoDeTraslado.tpl";
       break;
   }
 }
?> 
