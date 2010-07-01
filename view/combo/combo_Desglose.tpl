<?php 
 /** 
   * @name: Combobox of Desglose 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Desglose";
 if (!isset($name)) $name = "combo-Desglose";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-desglose", $default, "Desglose", true);
?>