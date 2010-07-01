<?php 
 /** 
   * @name: Combobox of RestriccionRealDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeBrigada";
 if (!isset($name)) $name = "combo-RestriccionRealDeBrigada";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_brigada", $default, "Restricci&oacute;n real de brigada", true);
?>