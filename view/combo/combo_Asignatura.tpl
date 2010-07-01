<?php 
 /** 
   * @name: Combobox of Asignatura 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Asignatura";
 if (!isset($name)) $name = "combo-Asignatura";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-asignatura", $default, "Asignatura", true);
?>