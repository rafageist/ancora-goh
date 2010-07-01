<?php 
 /** 
   * @name: Combobox of RestriccionDePeriodo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDePeriodo";
 if (!isset($name)) $name = "combo-RestriccionDePeriodo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_periodo", $default, "Restricci&oacute;n de periodo", true);
?>