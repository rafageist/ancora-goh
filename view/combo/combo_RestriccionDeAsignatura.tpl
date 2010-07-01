<?php 
 /** 
   * @name: Combobox of RestriccionDeAsignatura 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeAsignatura";
 if (!isset($name)) $name = "combo-RestriccionDeAsignatura";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_asignatura", $default, "Restricci&oacute;n de asignatura", true);
?>