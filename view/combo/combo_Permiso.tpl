<?php 
 /** 
   * @name: Combobox of Permiso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Permiso";
 if (!isset($name)) $name = "combo-Permiso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-permiso", $default, "Permiso", true);
?>