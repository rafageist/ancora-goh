<?php 
 /** 
   * @name: Combobox of RestriccionDeEspecialidad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeEspecialidad";
 if (!isset($name)) $name = "combo-RestriccionDeEspecialidad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_especialidad", $default, "Restricci&oacute;n de especialidad", true);
?>