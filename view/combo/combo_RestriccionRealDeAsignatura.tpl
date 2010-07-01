<?php 
 /** 
   * @name: Combobox of RestriccionRealDeAsignatura 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeAsignatura";
 if (!isset($name)) $name = "combo-RestriccionRealDeAsignatura";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_asignatura", $default, "Restricci&oacute;n real de asignatura", true);
?>