<?php 
 /** 
   * @name: Combobox of PlantillaDeActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:15 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-PlantillaDeActividad";
 if (!isset($name)) $name = "combo-PlantillaDeActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-plantilla_de_actividad", $default, "Plantilla de actividad", true);
?>