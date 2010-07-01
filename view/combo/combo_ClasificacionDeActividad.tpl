<?php 
 /** 
   * @name: Combobox of ClasificacionDeActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-ClasificacionDeActividad";
 if (!isset($name)) $name = "combo-ClasificacionDeActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-clasificacion_de_actividad", $default, "Clasificaci&oacute;n de actividad", true);
?>