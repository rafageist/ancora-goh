<?php 
 /** 
   * @name: Combobox of LugarParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-LugarParaActividad";
 if (!isset($name)) $name = "combo-LugarParaActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-lugar_para_actividad", $default, "Lugar para actividad", true);
?>