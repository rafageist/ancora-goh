<?php 
 /** 
   * @name: Combobox of Nivel 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Nivel";
 if (!isset($name)) $name = "combo-Nivel";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-nivel", $default, "Nivel", true);
?>