<?php 
 /** 
   * @name: Combobox of RestriccionPorActividadDeBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionPorActividadDeBrigada";
 if (!isset($name)) $name = "combo-RestriccionPorActividadDeBrigada";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_por_actividad_de_brigada", $default, "Restricci&oacute;n por actividad de brigada", true);
?>