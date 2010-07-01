<?php 
 /** 
   * @name: Combobox of HerenciaDeRestricciones 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-HerenciaDeRestricciones";
 if (!isset($name)) $name = "combo-HerenciaDeRestricciones";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-herencia_de_restricciones", $default, "Herencia de restricci&oacute;nes", true);
?>