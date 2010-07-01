<?php 
 /** 
   * @name: Combobox of Actividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Actividad";
 if (!isset($name)) $name = "combo-Actividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-actividad", $default, "Actividad", true);
?>