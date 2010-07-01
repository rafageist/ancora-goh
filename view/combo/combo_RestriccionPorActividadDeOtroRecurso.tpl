<?php 
 /** 
   * @name: Combobox of RestriccionPorActividadDeOtroRecurso 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionPorActividadDeOtroRecurso";
 if (!isset($name)) $name = "combo-RestriccionPorActividadDeOtroRecurso";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_por_actividad_de_otro_recurso", $default, "Restricci&oacute;n por actividad de otro recurso", true);
?>