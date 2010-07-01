<?php 
 /** 
   * @name: Combobox of RestriccionRealDeProfesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeProfesor";
 if (!isset($name)) $name = "combo-RestriccionRealDeProfesor";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_profesor", $default, "Restricci&oacute;n real de profesor", true);
?>