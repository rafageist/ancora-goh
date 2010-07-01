<?php 
 /** 
   * @name: Combobox of Especialidad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Especialidad";
 if (!isset($name)) $name = "combo-Especialidad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-especialidad", $default, "Especialidad", true);
?>