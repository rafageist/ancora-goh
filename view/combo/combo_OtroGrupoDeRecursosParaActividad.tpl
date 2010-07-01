<?php 
 /** 
   * @name: Combobox of OtroGrupoDeRecursosParaActividad 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:14 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-OtroGrupoDeRecursosParaActividad";
 if (!isset($name)) $name = "combo-OtroGrupoDeRecursosParaActividad";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-otro_grupo_de_recursos_para_actividad", $default, "Otro grupo de recursos para actividad", true);
?>