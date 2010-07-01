<?php 
 /** 
   * @name: Combobox of RestriccionDeProfesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeProfesor";
 if (!isset($name)) $name = "combo-RestriccionDeProfesor";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_profesor", $default, "Restricci&oacute;n de profesor", true);
?>