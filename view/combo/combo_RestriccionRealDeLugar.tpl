<?php 
 /** 
   * @name: Combobox of RestriccionRealDeLugar 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionRealDeLugar";
 if (!isset($name)) $name = "combo-RestriccionRealDeLugar";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_real_de_lugar", $default, "Restricci&oacute;n real de lugar", true);
?>