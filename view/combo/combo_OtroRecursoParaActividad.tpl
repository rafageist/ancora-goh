<?php 
 /** 
   * @name: Combobox of OtroRecursoParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-OtroRecursoParaActividad";
 if (!isset($name)) $name = "combo-OtroRecursoParaActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-otro_recurso_para_actividad", $default, "Otro recurso para actividad", true);
?>