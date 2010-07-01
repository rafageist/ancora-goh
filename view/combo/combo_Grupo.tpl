<?php 
 /** 
   * @name: Combobox of Grupo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Grupo";
 if (!isset($name)) $name = "combo-Grupo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-grupo", $default, "Grupo", true);
?>