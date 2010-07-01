<?php 
 /** 
   * @name: Combobox of RestriccionDeOtroRecurso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeOtroRecurso";
 if (!isset($name)) $name = "combo-RestriccionDeOtroRecurso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_otro_recurso", $default, "Restricci&oacute;n de otro recurso", true);
?>