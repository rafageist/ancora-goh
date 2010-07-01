<?php 
 /** 
   * @name: Combobox of ProfesorParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-ProfesorParaActividad";
 if (!isset($name)) $name = "combo-ProfesorParaActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-profesor_para_actividad", $default, "Profesor para actividad", true);
?>