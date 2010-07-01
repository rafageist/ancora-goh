<?php 
 /** 
   * @name: Combobox of HigieneParaBrigada 
   * @desc: Part View of MVC pattern 
   * @updated: 20/06/10 10:41:13 
   * @version: 1.0 
   */ 

 if (!isset($id)) $id = "combo-HigieneParaBrigada";
 if (!isset($name)) $name = "combo-HigieneParaBrigada";
 if (!isset($default)) $default = null;

 echo getHTMLCombobox($name, $id, $options, "combobox-higiene_para_brigada", $default, "Higiene para brigada", true);
?>