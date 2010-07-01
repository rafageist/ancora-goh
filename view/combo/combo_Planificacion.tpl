<?php 
 /** 
   * @name: Combobox of Planificacion 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-Planificacion";
 if (!isset($name)) $name = "combo-Planificacion";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-planificacion", $default, "Planificaci&oacute;n", true);
?>