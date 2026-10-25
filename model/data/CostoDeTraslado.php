<?php 
   class CostoDeTraslado{ 
       var $origen; 
       var $destino; 
       var $costo; 
       var $data;  
          /** 
            * @name: CostoDeTraslado 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($origen,$destino,$costo,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new CostoDeTrasladoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($origen,$destino,$costo); 
                    $this->origen = $origen; 
                    $this->destino = $destino; 
                    $this->costo = $costo; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($origen,$destino); 
                  $this->origen = $r->origen;
                  $this->destino = $r->destino;
                  $this->costo = $r->costo;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->origen = $origen;
                  $this->destino = $destino;
                  $this->costo = $costo;
              }
       } 
          /** 
            * @name: setOrigen 
            * @desc: Set value of property "Origen" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setOrigen($value){ 
              $this->origen = $value; 
              $this->data->setBy("origen = '".$this->origen."' AND destino = '".$this->destino."'", "origen = '$value'"); 
       } 
          /** 
            * @name: getOrigen 
            * @desc: Get value of property "Origen" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getOrigen(){ 
              $r = $this->data->get($this->origen,$this->destino); 
              $this->origen = $r->origen; 
              return $this->origen;
       } 
          /** 
            * @name: setDestino 
            * @desc: Set value of property "Destino" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDestino($value){ 
              $this->destino = $value; 
              $this->data->setBy("origen = '".$this->origen."' AND destino = '".$this->destino."'", "destino = '$value'"); 
       } 
          /** 
            * @name: getDestino 
            * @desc: Get value of property "Destino" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDestino(){ 
              $r = $this->data->get($this->origen,$this->destino); 
              $this->destino = $r->destino; 
              return $this->destino;
       } 
          /** 
            * @name: setCosto 
            * @desc: Set value of property "Costo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCosto($value){ 
              $this->costo = $value; 
              $this->data->setBy("origen = '".$this->origen."' AND destino = '".$this->destino."'", "costo = $value"); 
       } 
          /** 
            * @name: getCosto 
            * @desc: Get value of property "Costo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCosto(){ 
              $r = $this->data->get($this->origen,$this->destino); 
              $this->costo = $r->costo; 
              return $this->costo;
       } 
} 
?>