<?php 
 /** 
   * @name: Combobox of AccesoProhibido 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-AccesoProhibido";
 if (!isset($name)) $name = "combo-AccesoProhibido";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-acceso_prohibido", $default, "Acceso prohibido", true);
?>