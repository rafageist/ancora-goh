<?php 
 /** 
   * @name: Combobox of Profesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Profesor";
 if (!isset($name)) $name = "combo-Profesor";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-profesor", $default, "Profesor", true);
?>