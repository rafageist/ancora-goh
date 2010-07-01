<?php 
 /** 
   * @name: Combobox of Zpriori 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Zpriori";
 if (!isset($name)) $name = "combo-Zpriori";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-zpriori", $default, "Zpriori", true);
?>