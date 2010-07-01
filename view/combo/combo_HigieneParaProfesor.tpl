<?php 
 /** 
   * @name: Combobox of HigieneParaProfesor 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-HigieneParaProfesor";
 if (!isset($name)) $name = "combo-HigieneParaProfesor";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-higiene_para_profesor", $default, "Higiene para profesor", true);
?>