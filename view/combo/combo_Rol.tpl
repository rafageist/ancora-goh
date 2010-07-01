<?php 
 /** 
   * @name: Combobox of Rol 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:16 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Rol";
 if (!isset($name)) $name = "combo-Rol";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-rol", $default, "Rol", true);
?>