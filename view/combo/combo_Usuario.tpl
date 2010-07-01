<?php 
 /** 
   * @name: Combobox of Usuario 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Usuario";
 if (!isset($name)) $name = "combo-Usuario";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-usuario", $default, "Usuario", true);
?>