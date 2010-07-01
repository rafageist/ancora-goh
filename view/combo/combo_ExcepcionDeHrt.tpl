<?php 
 /** 
   * @name: Combobox of ExcepcionDeHrt 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-ExcepcionDeHrt";
 if (!isset($name)) $name = "combo-ExcepcionDeHrt";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-excepcion_de_hrt", $default, "Excepci&oacute;n de hrt", true);
?>