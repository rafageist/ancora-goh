<?php 
 /** 
   * @name: Combobox of Periodicidad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Periodicidad";
 if (!isset($name)) $name = "combo-Periodicidad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-periodicidad", $default, "Periodicidad", true);
?>