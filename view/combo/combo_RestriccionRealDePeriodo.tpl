<?php 
 /** 
   * @name: Combobox of RestriccionRealDePeriodo 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDePeriodo";
 if (!isset($name)) $name = "combo-RestriccionRealDePeriodo";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_periodo", $default, "Restricci&oacute;n real de periodo", true);
?>