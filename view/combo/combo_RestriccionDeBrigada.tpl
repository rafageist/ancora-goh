<?php 
 /** 
   * @name: Combobox of RestriccionDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeBrigada";
 if (!isset($name)) $name = "combo-RestriccionDeBrigada";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_brigada", $default, "Restricci&oacute;n de brigada", true);
?>