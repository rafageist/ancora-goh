<?php 
 /** 
   * @name: Combobox of RestriccionDeLugar 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionDeLugar";
 if (!isset($name)) $name = "combo-RestriccionDeLugar";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_de_lugar", $default, "Restricci&oacute;n de lugar", true);
?>