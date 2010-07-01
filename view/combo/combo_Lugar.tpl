<?php 
 /** 
   * @name: Combobox of Lugar 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Lugar";
 if (!isset($name)) $name = "combo-Lugar";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-lugar", $default, "Lugar", true);
?>