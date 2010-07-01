<?php 
   class ProfesorParaGrupo{ 
       var $profesor_para_actividad; 
       var $grupo; 
       var $data;  
          /** 
            * @name: ProfesorParaGrupo 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function ProfesorParaGrupo($profesor_para_actividad,$grupo,$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new ProfesorParaGrupoModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($profesor_para_actividad,$grupo); 
                    $this->profesor_para_actividad = $profesor_para_actividad; 
                    $this->grupo = $grupo; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($profesor_para_actividad,$grupo); 
                  $this->profesor_para_actividad = $r->profesor_para_actividad;
                  $this->grupo = $r->grupo;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->profesor_para_actividad = $profesor_para_actividad;
                  $this->grupo = $grupo;
              }
       } 
          /** 
            * @name: setProfesorParaActividad 
            * @desc: Set value of property "ProfesorParaActividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setProfesorParaActividad($value){ 
              $this->profesor_para_actividad = $value; 
              $this->data->setBy("profesor_para_actividad = '".$this->profesor_para_actividad."' AND grupo = '".$this->grupo."'", "profesor_para_actividad = '$value'"); 
       } 
          /** 
            * @name: getProfesorParaActividad 
            * @desc: Get value of property "ProfesorParaActividad" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getProfesorParaActividad(){ 
              $r = $this->data->get($this->profesor_para_actividad,$this->grupo); 
              $this->profesor_para_actividad = $r->profesor_para_actividad; 
              return $this->profesor_para_actividad;
       } 
          /** 
            * @name: setGrupo 
            * @desc: Set value of property "Grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function setGrupo($value){ 
              $this->grupo = $value; 
              $this->data->setBy("profesor_para_actividad = '".$this->profesor_para_actividad."' AND grupo = '".$this->grupo."'", "grupo = '$value'"); 
       } 
          /** 
            * @name: getGrupo 
            * @desc: Get value of property "Grupo" 
            * @updated: 20/06/10 10:40:55 
            * @version: 1.0 
            */ 
       public function getGrupo(){ 
              $r = $this->data->get($this->profesor_para_actividad,$this->grupo); 
              $this->grupo = $r->grupo; 
              return $this->grupo;
       } 
} 
?>