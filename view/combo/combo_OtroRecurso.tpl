<?php 
 /** 
   * @name: Combobox of OtroRecurso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-OtroRecurso";
 if (!isset($name)) $name = "combo-OtroRecurso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-otro_recurso", $default, "Otro recurso", true);
?>