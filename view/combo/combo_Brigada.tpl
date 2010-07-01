<?php 
 /** 
   * @name: Combobox of Brigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Brigada";
 if (!isset($name)) $name = "combo-Brigada";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-brigada", $default, "Brigada", true);
?>