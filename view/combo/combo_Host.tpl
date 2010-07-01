<?php 
 /** 
   * @name: Combobox of Host 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Host";
 if (!isset($name)) $name = "combo-Host";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-host", $default, "Host", true);
?>