<?php 
   class OtroRecursoParaActividad{ 
       var $grupo_de_recursos; 
       var $prioridad; 
       var $otro_recurso; 
       var $data;  
          /** 
            * @name: OtroRecursoParaActividad 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function OtroRecursoParaActividad($grupo_de_recursos,$prioridad,$otro_recurso,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new OtroRecursoParaActividadModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($grupo_de_recursos,$prioridad,$otro_recurso); 
                    $this->grupo_de_recursos = $grupo_de_recursos; 
                    $this->prioridad = $prioridad; 
                    $this->otro_recurso = $otro_recurso; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($grupo_de_recursos,$otro_recurso); 
                  $this->grupo_de_recursos = $r->grupo_de_recursos;
                  $this->prioridad = $r->prioridad;
                  $this->otro_recurso = $r->otro_recurso;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->grupo_de_recursos = $grupo_de_recursos;
                  $this->prioridad = $prioridad;
                  $this->otro_recurso = $otro_recurso;
              }
       } 
          /** 
            * @name: setGrupoDeRecursos 
            * @desc: Set value of property "GrupoDeRecursos" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setGrupoDeRecursos($value){ 
              $this->grupo_de_recursos = $value; 
              $this->data->setBy("grupo_de_recursos = '".$this->grupo_de_recursos."' AND otro_recurso = '".$this->otro_recurso."'", "grupo_de_recursos = '$value'"); 
       } 
          /** 
            * @name: getGrupoDeRecursos 
            * @desc: Get value of property "GrupoDeRecursos" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getGrupoDeRecursos(){ 
              $r = $this->data->get($this->grupo_de_recursos,$this->otro_recurso); 
              $this->grupo_de_recursos = $r->grupo_de_recursos; 
              return $this->grupo_de_recursos;
       } 
          /** 
            * @name: setPrioridad 
            * @desc: Set value of property "Prioridad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setPrioridad($value){ 
              $this->prioridad = $value; 
              $this->data->setBy("grupo_de_recursos = '".$this->grupo_de_recursos."' AND otro_recurso = '".$this->otro_recurso."'", "prioridad = $value"); 
       } 
          /** 
            * @name: getPrioridad 
            * @desc: Get value of property "Prioridad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getPrioridad(){ 
              $r = $this->data->get($this->grupo_de_recursos,$this->otro_recurso); 
              $this->prioridad = $r->prioridad; 
              return $this->prioridad;
       } 
          /** 
            * @name: setOtroRecurso 
            * @desc: Set value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setOtroRecurso($value){ 
              $this->otro_recurso = $value; 
              $this->data->setBy("grupo_de_recursos = '".$this->grupo_de_recursos."' AND otro_recurso = '".$this->otro_recurso."'", "otro_recurso = '$value'"); 
       } 
          /** 
            * @name: getOtroRecurso 
            * @desc: Get value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getOtroRecurso(){ 
              $r = $this->data->get($this->grupo_de_recursos,$this->otro_recurso); 
              $this->otro_recurso = $r->otro_recurso; 
              return $this->otro_recurso;
       } 
} 
?>