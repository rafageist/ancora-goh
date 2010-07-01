<?php 
 /** 
   * @name: Combobox of LugarFijo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-LugarFijo";
 if (!isset($name)) $name = "combo-LugarFijo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-lugar_fijo", $default, "Lugar fijo", true);
?>