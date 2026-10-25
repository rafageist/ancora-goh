<?php 
   class HerenciaDeRestricciones{ 
       var $id; 
       var $planificacion; 
       var $activa; 
       var $hijo_brigada; 
       var $hijo_profesor; 
       var $hijo_lugar; 
       var $hijo_otro_recurso; 
       var $padre_brigada; 
       var $padre_profesor; 
       var $padre_lugar; 
       var $padre_otro_recurso; 
       var $data;  
          /** 
            * @name: HerenciaDeRestricciones 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new HerenciaDeRestriccionesModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($planificacion,$activa,$hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso); 
                    $this->id = $replicas["id"]; 
                    $this->planificacion = $planificacion; 
                    $this->activa = $activa; 
                    $this->hijo_brigada = $hijo_brigada; 
                    $this->hijo_profesor = $hijo_profesor; 
                    $this->hijo_lugar = $hijo_lugar; 
                    $this->hijo_otro_recurso = $hijo_otro_recurso; 
                    $this->padre_brigada = $padre_brigada; 
                    $this->padre_profesor = $padre_profesor; 
                    $this->padre_lugar = $padre_lugar; 
                    $this->padre_otro_recurso = $padre_otro_recurso; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->planificacion = $r->planificacion;
                  $this->activa = $r->activa;
                  $this->hijo_brigada = $r->hijo_brigada;
                  $this->hijo_profesor = $r->hijo_profesor;
                  $this->hijo_lugar = $r->hijo_lugar;
                  $this->hijo_otro_recurso = $r->hijo_otro_recurso;
                  $this->padre_brigada = $r->padre_brigada;
                  $this->padre_profesor = $r->padre_profesor;
                  $this->padre_lugar = $r->padre_lugar;
                  $this->padre_otro_recurso = $r->padre_otro_recurso;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->planificacion = $planificacion;
                  $this->activa = $activa;
                  $this->hijo_brigada = $hijo_brigada;
                  $this->hijo_profesor = $hijo_profesor;
                  $this->hijo_lugar = $hijo_lugar;
                  $this->hijo_otro_recurso = $hijo_otro_recurso;
                  $this->padre_brigada = $padre_brigada;
                  $this->padre_profesor = $padre_profesor;
                  $this->padre_lugar = $padre_lugar;
                  $this->padre_otro_recurso = $padre_otro_recurso;
              }
       } 
          /** 
            * @name: getId 
            * @desc: Get value of property "Id" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getId(){ 
              $r = $this->data->get($this->id); 
              $this->id = $r->id; 
              return $this->id;
       } 
          /** 
            * @name: setPlanificacion 
            * @desc: Set value of property "Planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPlanificacion($value){ 
              $this->planificacion = $value; 
              $this->data->setBy("id = '".$this->id."'", "planificacion = '$value'"); 
       } 
          /** 
            * @name: getPlanificacion 
            * @desc: Get value of property "Planificacion" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPlanificacion(){ 
              $r = $this->data->get($this->id); 
              $this->planificacion = $r->planificacion; 
              return $this->planificacion;
       } 
          /** 
            * @name: setActiva 
            * @desc: Set value of property "Activa" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setActiva($value){ 
              $this->activa = $value; 
               if ($value == true || $value == 1 || $value == "t") $value = "true"; 
               else if ($value == true || $value == 0 || $value == "f") $value = "false"; 
              $this->data->setBy("id = '".$this->id."'", "activa = $value"); 
       } 
          /** 
            * @name: getActiva 
            * @desc: Get value of property "Activa" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getActiva(){ 
              $r = $this->data->get($this->id); 
              $this->activa = $r->activa; 
              return $this->activa;
       } 
          /** 
            * @name: setHijoBrigada 
            * @desc: Set value of property "HijoBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHijoBrigada($value){ 
              $this->hijo_brigada = $value; 
              $this->data->setBy("id = '".$this->id."'", "hijo_brigada = '$value'"); 
       } 
          /** 
            * @name: getHijoBrigada 
            * @desc: Get value of property "HijoBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHijoBrigada(){ 
              $r = $this->data->get($this->id); 
              $this->hijo_brigada = $r->hijo_brigada; 
              return $this->hijo_brigada;
       } 
          /** 
            * @name: setHijoProfesor 
            * @desc: Set value of property "HijoProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHijoProfesor($value){ 
              $this->hijo_profesor = $value; 
              $this->data->setBy("id = '".$this->id."'", "hijo_profesor = '$value'"); 
       } 
          /** 
            * @name: getHijoProfesor 
            * @desc: Get value of property "HijoProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHijoProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->hijo_profesor = $r->hijo_profesor; 
              return $this->hijo_profesor;
       } 
          /** 
            * @name: setHijoLugar 
            * @desc: Set value of property "HijoLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHijoLugar($value){ 
              $this->hijo_lugar = $value; 
              $this->data->setBy("id = '".$this->id."'", "hijo_lugar = '$value'"); 
       } 
          /** 
            * @name: getHijoLugar 
            * @desc: Get value of property "HijoLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHijoLugar(){ 
              $r = $this->data->get($this->id); 
              $this->hijo_lugar = $r->hijo_lugar; 
              return $this->hijo_lugar;
       } 
          /** 
            * @name: setHijoOtroRecurso 
            * @desc: Set value of property "HijoOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHijoOtroRecurso($value){ 
              $this->hijo_otro_recurso = $value; 
              $this->data->setBy("id = '".$this->id."'", "hijo_otro_recurso = '$value'"); 
       } 
          /** 
            * @name: getHijoOtroRecurso 
            * @desc: Get value of property "HijoOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHijoOtroRecurso(){ 
              $r = $this->data->get($this->id); 
              $this->hijo_otro_recurso = $r->hijo_otro_recurso; 
              return $this->hijo_otro_recurso;
       } 
          /** 
            * @name: setPadreBrigada 
            * @desc: Set value of property "PadreBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPadreBrigada($value){ 
              $this->padre_brigada = $value; 
              $this->data->setBy("id = '".$this->id."'", "padre_brigada = '$value'"); 
       } 
          /** 
            * @name: getPadreBrigada 
            * @desc: Get value of property "PadreBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPadreBrigada(){ 
              $r = $this->data->get($this->id); 
              $this->padre_brigada = $r->padre_brigada; 
              return $this->padre_brigada;
       } 
          /** 
            * @name: setPadreProfesor 
            * @desc: Set value of property "PadreProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPadreProfesor($value){ 
              $this->padre_profesor = $value; 
              $this->data->setBy("id = '".$this->id."'", "padre_profesor = '$value'"); 
       } 
          /** 
            * @name: getPadreProfesor 
            * @desc: Get value of property "PadreProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPadreProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->padre_profesor = $r->padre_profesor; 
              return $this->padre_profesor;
       } 
          /** 
            * @name: setPadreLugar 
            * @desc: Set value of property "PadreLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPadreLugar($value){ 
              $this->padre_lugar = $value; 
              $this->data->setBy("id = '".$this->id."'", "padre_lugar = '$value'"); 
       } 
          /** 
            * @name: getPadreLugar 
            * @desc: Get value of property "PadreLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPadreLugar(){ 
              $r = $this->data->get($this->id); 
              $this->padre_lugar = $r->padre_lugar; 
              return $this->padre_lugar;
       } 
          /** 
            * @name: setPadreOtroRecurso 
            * @desc: Set value of property "PadreOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPadreOtroRecurso($value){ 
              $this->padre_otro_recurso = $value; 
              $this->data->setBy("id = '".$this->id."'", "padre_otro_recurso = '$value'"); 
       } 
          /** 
            * @name: getPadreOtroRecurso 
            * @desc: Get value of property "PadreOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPadreOtroRecurso(){ 
              $r = $this->data->get($this->id); 
              $this->padre_otro_recurso = $r->padre_otro_recurso; 
              return $this->padre_otro_recurso;
       } 
       public function addExcepcionDeHrt($periodo,$dia,$turno){ 
              $herencia = $this->id; 
              $obj = new ExcepcionDeHrtClass($herencia,$periodo,$dia,$turno,"",true,false); 
              return $obj;
       } 
       public function getExcepcionDeHrt(){ 
          $collection = new ExcepcionDeHrtCollection($this->data->db);
          $collection->Load(null,null,"*","herencia = '".$this->id."'");
           return $collection;
       } 
} 
?>