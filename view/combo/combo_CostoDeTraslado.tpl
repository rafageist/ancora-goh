<?php 
 /** 
   * @name: Combobox of CostoDeTraslado 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-CostoDeTraslado";
 if (!isset($name)) $name = "combo-CostoDeTraslado";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-costo_de_traslado", $default, "Costo de traslado", true);
?>