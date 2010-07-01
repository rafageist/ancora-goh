<?php 
 /** 
   * @name: Combobox views of OtroRecursoParaActividad 
   * @desc: Part Control of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 import ("model.data.OtroRecursoParaActividadModel");
 import ("model.data.OtroRecursoParaActividad");
 import ("model.data.OtroRecursoParaActividadCollection");

 $collection = new OtroRecursoParaActividadCollection($dbconnection);
 $collection->Load();

 if (isset($_POST["id"])) $indentity = $_POST["id"];
 if (isset($_POST["name"])) $name = $_POST["name"];
 if (isset($_POST["default"])) $default = $_POST["default"];

 $options = array();
 foreach($collection->collection as $item){
     $options['{'.$item->grupo_de_recursos.','.$item->otro_recurso.'}'] = (isset($item->descripcion)?$item->descripcion:"");
 }

 if (isset($_POST["forma"])){ 
   switch($_POST["forma"]){
     case "combo":
       include "../view/combo/combo_OtroRecursoParaActividad.tpl";
       break;
   }
 }
?> 
