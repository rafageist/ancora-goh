<?php 

/** 
  * @name: Receptor of CostoDeTraslado data from POST for INSERT 
  * @desc: Part Control of MVC Pattern 
  * @updated: 20/06/10 10:41:13 
  * @version: 1.0 
  */ 
import("model.data.CostoDeTrasladoModel"); 
import("model.data.CostoDeTraslado"); 

// Execute fields's modification data 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_origen.php")) include "../control/data/fdms/fdm_CostoDeTraslado_origen.php"; 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_destino.php")) include "../control/data/fdms/fdm_CostoDeTraslado_destino.php"; 
   if (file_exists("../control/data/fdms/fdm_CostoDeTraslado_costo.php")) include "../control/data/fdms/fdm_CostoDeTraslado_costo.php"; 

// Getting data from POST 
   $origen = $_POST["field_CostoDeTraslado_origen"];
   $destino = $_POST["field_CostoDeTraslado_destino"];
   $costo = $_POST["field_CostoDeTraslado_costo"];

 // Construyendo el objeto 
   $obj = new CostoDeTraslado($origen,$destino,$costo,true, null, false);
?>