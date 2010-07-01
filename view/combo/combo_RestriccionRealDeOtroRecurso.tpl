<?php 
 /** 
   * @name: Combobox of RestriccionRealDeOtroRecurso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeOtroRecurso";
 if (!isset($name)) $name = "combo-RestriccionRealDeOtroRecurso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_otro_recurso", $default, "Restricci&oacute;n real de otro recurso", true);
?>