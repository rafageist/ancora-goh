<?php 
 /** 
   * @name: Combobox of RestriccionPorActividadDeProfesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-RestriccionPorActividadDeProfesor";
 if (!isset($name)) $name = "combo-RestriccionPorActividadDeProfesor";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-restriccion_por_actividad_de_profesor", $default, "Restricci&oacute;n por actividad de profesor", true);
?>