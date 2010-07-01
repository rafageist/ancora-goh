<?php 
 /** 
   * @name: Combobox of Periodo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Periodo";
 if (!isset($name)) $name = "combo-Periodo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-periodo", $default, "Periodo", true);
?>