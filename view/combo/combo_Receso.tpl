<?php 
 /** 
   * @name: Combobox of Receso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Receso";
 if (!isset($name)) $name = "combo-Receso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-receso", $default, "Receso", true);
?>