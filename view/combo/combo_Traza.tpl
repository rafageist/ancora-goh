<?php 
 /** 
   * @name: Combobox of Traza 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Traza";
 if (!isset($name)) $name = "combo-Traza";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-traza", $default, "Traza", true);
?>