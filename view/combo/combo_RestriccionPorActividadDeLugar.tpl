<?php 
 /** 
   * @name: Combobox of RestriccionPorActividadDeLugar 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionPorActividadDeLugar";
 if (!isset($name)) $name = "combo-RestriccionPorActividadDeLugar";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_por_actividad_de_lugar", $default, "Restricci&oacute;n por actividad de lugar", true);
?>