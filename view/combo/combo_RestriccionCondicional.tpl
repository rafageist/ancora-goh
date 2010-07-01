<?php 
 /** 
   * @name: Combobox of RestriccionCondicional 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionCondicional";
 if (!isset($name)) $name = "combo-RestriccionCondicional";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_condicional", $default, "Restricci&oacute;n condici&oacute;nal", true);
?>