<?php 
 /** 
   * @name: Combobox of RestriccionPorActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionPorActividad";
 if (!isset($name)) $name = "combo-RestriccionPorActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_por_actividad", $default, "Restricci&oacute;n por actividad", true);
?>