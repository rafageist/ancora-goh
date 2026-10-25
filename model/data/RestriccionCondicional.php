<?php 
   class RestriccionCondicional{ 
       var $id; 
       var $planificacion; 
       var $activa; 
       var $clasif_act; 
       var $asig; 
       var $brigada; 
       var $profesor; 
       var $lugar; 
       var $otro_recurso; 
       var $especialidad; 
       var $nivel; 
       var $fecha_ini; 
       var $fecha_fin; 
       var $hora_ini; 
       var $hora_fin; 
       var $discreto_exacto; 
       var $dia_ini_planif; 
       var $dia_fin_planif; 
       var $hora_ini_planif; 
       var $hora_fin_planif; 
       var $discreto_exacto_planif; 
       var $periodo; 
       var $dia_ini; 
       var $dia_fin; 
       var $turno_ini; 
       var $turno_fin; 
       var $discreto; 
       var $hoy; 
       var $ahora; 
       var $dia_relativo_anterior; 
       var $cant_dias_relativo_anterior; 
       var $dia_relativo_posterior; 
       var $cant_dias_relativo_posterior; 
       var $rango_tiempo_relativo_anterior_ini; 
       var $rango_tiempo_relativo_anterior_tamano; 
       var $turno_relativo_anterior; 
       var $cant_turnos_relativo_anterior; 
       var $turno_relativo_posterior; 
       var $cant_turnos_relativo_posterior; 
       var $rango_tiempo_relativo_posterior_ini; 
       var $rango_tiempo_relativo_posterior_tamano; 
       var $entre_dias_antes; 
       var $entre_dias_despues; 
       var $entre_turnos_antes; 
       var $entre_turnos_despues; 
       var $entre_tiempo_antes; 
       var $entre_tiempo_despues; 
       var $cant_exa; 
       var $cant_min; 
       var $cant_max; 
       var $consec_brigada; 
       var $consec_profesor; 
       var $consec_lugar; 
       var $consec_otro_recurso; 
       var $consec_periodo; 
       var $consec_dia_ini; 
       var $consec_dia_fin; 
       var $consec_turno_ini; 
       var $consec_turno_fin; 
       var $consec_discreto; 
       var $siempre; 
       var $valor; 
       var $data;  
          /** 
            * @name: RestriccionCondicional 
            * @desc: Class constructor 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function __construct($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor,$id = '',$construct = true, $data = false, $load = true){ 
              global $dbconnection;
              if ($data == false) $this->data = new RestriccionCondicionalModel($dbconnection); else $this->data = $data;

              //construir el objeto e insertarlo en la base de datos 
              if ($construct == true) { 
                  $replicas = $this->data->add($planificacion,$activa,$clasif_act,$asig,$brigada,$profesor,$lugar,$otro_recurso,$especialidad,$nivel,$fecha_ini,$fecha_fin,$hora_ini,$hora_fin,$discreto_exacto,$dia_ini_planif,$dia_fin_planif,$hora_ini_planif,$hora_fin_planif,$discreto_exacto_planif,$periodo,$dia_ini,$dia_fin,$turno_ini,$turno_fin,$discreto,$hoy,$ahora,$dia_relativo_anterior,$cant_dias_relativo_anterior,$dia_relativo_posterior,$cant_dias_relativo_posterior,$rango_tiempo_relativo_anterior_ini,$rango_tiempo_relativo_anterior_tamano,$turno_relativo_anterior,$cant_turnos_relativo_anterior,$turno_relativo_posterior,$cant_turnos_relativo_posterior,$rango_tiempo_relativo_posterior_ini,$rango_tiempo_relativo_posterior_tamano,$entre_dias_antes,$entre_dias_despues,$entre_turnos_antes,$entre_turnos_despues,$entre_tiempo_antes,$entre_tiempo_despues,$cant_exa,$cant_min,$cant_max,$consec_brigada,$consec_profesor,$consec_lugar,$consec_otro_recurso,$consec_periodo,$consec_dia_ini,$consec_dia_fin,$consec_turno_ini,$consec_turno_fin,$consec_discreto,$siempre,$valor); 
                    $this->id = $replicas["id"]; 
                    $this->planificacion = $planificacion; 
                    $this->activa = $activa; 
                    $this->clasif_act = $clasif_act; 
                    $this->asig = $asig; 
                    $this->brigada = $brigada; 
                    $this->profesor = $profesor; 
                    $this->lugar = $lugar; 
                    $this->otro_recurso = $otro_recurso; 
                    $this->especialidad = $especialidad; 
                    $this->nivel = $nivel; 
                    $this->fecha_ini = $fecha_ini; 
                    $this->fecha_fin = $fecha_fin; 
                    $this->hora_ini = $hora_ini; 
                    $this->hora_fin = $hora_fin; 
                    $this->discreto_exacto = $discreto_exacto; 
                    $this->dia_ini_planif = $dia_ini_planif; 
                    $this->dia_fin_planif = $dia_fin_planif; 
                    $this->hora_ini_planif = $hora_ini_planif; 
                    $this->hora_fin_planif = $hora_fin_planif; 
                    $this->discreto_exacto_planif = $discreto_exacto_planif; 
                    $this->periodo = $periodo; 
                    $this->dia_ini = $dia_ini; 
                    $this->dia_fin = $dia_fin; 
                    $this->turno_ini = $turno_ini; 
                    $this->turno_fin = $turno_fin; 
                    $this->discreto = $discreto; 
                    $this->hoy = $hoy; 
                    $this->ahora = $ahora; 
                    $this->dia_relativo_anterior = $dia_relativo_anterior; 
                    $this->cant_dias_relativo_anterior = $cant_dias_relativo_anterior; 
                    $this->dia_relativo_posterior = $dia_relativo_posterior; 
                    $this->cant_dias_relativo_posterior = $cant_dias_relativo_posterior; 
                    $this->rango_tiempo_relativo_anterior_ini = $rango_tiempo_relativo_anterior_ini; 
                    $this->rango_tiempo_relativo_anterior_tamano = $rango_tiempo_relativo_anterior_tamano; 
                    $this->turno_relativo_anterior = $turno_relativo_anterior; 
                    $this->cant_turnos_relativo_anterior = $cant_turnos_relativo_anterior; 
                    $this->turno_relativo_posterior = $turno_relativo_posterior; 
                    $this->cant_turnos_relativo_posterior = $cant_turnos_relativo_posterior; 
                    $this->rango_tiempo_relativo_posterior_ini = $rango_tiempo_relativo_posterior_ini; 
                    $this->rango_tiempo_relativo_posterior_tamano = $rango_tiempo_relativo_posterior_tamano; 
                    $this->entre_dias_antes = $entre_dias_antes; 
                    $this->entre_dias_despues = $entre_dias_despues; 
                    $this->entre_turnos_antes = $entre_turnos_antes; 
                    $this->entre_turnos_despues = $entre_turnos_despues; 
                    $this->entre_tiempo_antes = $entre_tiempo_antes; 
                    $this->entre_tiempo_despues = $entre_tiempo_despues; 
                    $this->cant_exa = $cant_exa; 
                    $this->cant_min = $cant_min; 
                    $this->cant_max = $cant_max; 
                    $this->consec_brigada = $consec_brigada; 
                    $this->consec_profesor = $consec_profesor; 
                    $this->consec_lugar = $consec_lugar; 
                    $this->consec_otro_recurso = $consec_otro_recurso; 
                    $this->consec_periodo = $consec_periodo; 
                    $this->consec_dia_ini = $consec_dia_ini; 
                    $this->consec_dia_fin = $consec_dia_fin; 
                    $this->consec_turno_ini = $consec_turno_ini; 
                    $this->consec_turno_fin = $consec_turno_fin; 
                    $this->consec_discreto = $consec_discreto; 
                    $this->siempre = $siempre; 
                    $this->valor = $valor; 
              } 
              else if ($load == true) { // cargar el objeto de la base de datos
                  $r = $this->data->get($id); 
                  $this->id = $r->id;
                  $this->planificacion = $r->planificacion;
                  $this->activa = $r->activa;
                  $this->clasif_act = $r->clasif_act;
                  $this->asig = $r->asig;
                  $this->brigada = $r->brigada;
                  $this->profesor = $r->profesor;
                  $this->lugar = $r->lugar;
                  $this->otro_recurso = $r->otro_recurso;
                  $this->especialidad = $r->especialidad;
                  $this->nivel = $r->nivel;
                  $this->fecha_ini = $r->fecha_ini;
                  $this->fecha_fin = $r->fecha_fin;
                  $this->hora_ini = $r->hora_ini;
                  $this->hora_fin = $r->hora_fin;
                  $this->discreto_exacto = $r->discreto_exacto;
                  $this->dia_ini_planif = $r->dia_ini_planif;
                  $this->dia_fin_planif = $r->dia_fin_planif;
                  $this->hora_ini_planif = $r->hora_ini_planif;
                  $this->hora_fin_planif = $r->hora_fin_planif;
                  $this->discreto_exacto_planif = $r->discreto_exacto_planif;
                  $this->periodo = $r->periodo;
                  $this->dia_ini = $r->dia_ini;
                  $this->dia_fin = $r->dia_fin;
                  $this->turno_ini = $r->turno_ini;
                  $this->turno_fin = $r->turno_fin;
                  $this->discreto = $r->discreto;
                  $this->hoy = $r->hoy;
                  $this->ahora = $r->ahora;
                  $this->dia_relativo_anterior = $r->dia_relativo_anterior;
                  $this->cant_dias_relativo_anterior = $r->cant_dias_relativo_anterior;
                  $this->dia_relativo_posterior = $r->dia_relativo_posterior;
                  $this->cant_dias_relativo_posterior = $r->cant_dias_relativo_posterior;
                  $this->rango_tiempo_relativo_anterior_ini = $r->rango_tiempo_relativo_anterior_ini;
                  $this->rango_tiempo_relativo_anterior_tamano = $r->rango_tiempo_relativo_anterior_tamano;
                  $this->turno_relativo_anterior = $r->turno_relativo_anterior;
                  $this->cant_turnos_relativo_anterior = $r->cant_turnos_relativo_anterior;
                  $this->turno_relativo_posterior = $r->turno_relativo_posterior;
                  $this->cant_turnos_relativo_posterior = $r->cant_turnos_relativo_posterior;
                  $this->rango_tiempo_relativo_posterior_ini = $r->rango_tiempo_relativo_posterior_ini;
                  $this->rango_tiempo_relativo_posterior_tamano = $r->rango_tiempo_relativo_posterior_tamano;
                  $this->entre_dias_antes = $r->entre_dias_antes;
                  $this->entre_dias_despues = $r->entre_dias_despues;
                  $this->entre_turnos_antes = $r->entre_turnos_antes;
                  $this->entre_turnos_despues = $r->entre_turnos_despues;
                  $this->entre_tiempo_antes = $r->entre_tiempo_antes;
                  $this->entre_tiempo_despues = $r->entre_tiempo_despues;
                  $this->cant_exa = $r->cant_exa;
                  $this->cant_min = $r->cant_min;
                  $this->cant_max = $r->cant_max;
                  $this->consec_brigada = $r->consec_brigada;
                  $this->consec_profesor = $r->consec_profesor;
                  $this->consec_lugar = $r->consec_lugar;
                  $this->consec_otro_recurso = $r->consec_otro_recurso;
                  $this->consec_periodo = $r->consec_periodo;
                  $this->consec_dia_ini = $r->consec_dia_ini;
                  $this->consec_dia_fin = $r->consec_dia_fin;
                  $this->consec_turno_ini = $r->consec_turno_ini;
                  $this->consec_turno_fin = $r->consec_turno_fin;
                  $this->consec_discreto = $r->consec_discreto;
                  $this->siempre = $r->siempre;
                  $this->valor = $r->valor;
              } 
              else { // construir el objeto a partir de los parametros
                  $this->id = $id;
                  $this->planificacion = $planificacion;
                  $this->activa = $activa;
                  $this->clasif_act = $clasif_act;
                  $this->asig = $asig;
                  $this->brigada = $brigada;
                  $this->profesor = $profesor;
                  $this->lugar = $lugar;
                  $this->otro_recurso = $otro_recurso;
                  $this->especialidad = $especialidad;
                  $this->nivel = $nivel;
                  $this->fecha_ini = $fecha_ini;
                  $this->fecha_fin = $fecha_fin;
                  $this->hora_ini = $hora_ini;
                  $this->hora_fin = $hora_fin;
                  $this->discreto_exacto = $discreto_exacto;
                  $this->dia_ini_planif = $dia_ini_planif;
                  $this->dia_fin_planif = $dia_fin_planif;
                  $this->hora_ini_planif = $hora_ini_planif;
                  $this->hora_fin_planif = $hora_fin_planif;
                  $this->discreto_exacto_planif = $discreto_exacto_planif;
                  $this->periodo = $periodo;
                  $this->dia_ini = $dia_ini;
                  $this->dia_fin = $dia_fin;
                  $this->turno_ini = $turno_ini;
                  $this->turno_fin = $turno_fin;
                  $this->discreto = $discreto;
                  $this->hoy = $hoy;
                  $this->ahora = $ahora;
                  $this->dia_relativo_anterior = $dia_relativo_anterior;
                  $this->cant_dias_relativo_anterior = $cant_dias_relativo_anterior;
                  $this->dia_relativo_posterior = $dia_relativo_posterior;
                  $this->cant_dias_relativo_posterior = $cant_dias_relativo_posterior;
                  $this->rango_tiempo_relativo_anterior_ini = $rango_tiempo_relativo_anterior_ini;
                  $this->rango_tiempo_relativo_anterior_tamano = $rango_tiempo_relativo_anterior_tamano;
                  $this->turno_relativo_anterior = $turno_relativo_anterior;
                  $this->cant_turnos_relativo_anterior = $cant_turnos_relativo_anterior;
                  $this->turno_relativo_posterior = $turno_relativo_posterior;
                  $this->cant_turnos_relativo_posterior = $cant_turnos_relativo_posterior;
                  $this->rango_tiempo_relativo_posterior_ini = $rango_tiempo_relativo_posterior_ini;
                  $this->rango_tiempo_relativo_posterior_tamano = $rango_tiempo_relativo_posterior_tamano;
                  $this->entre_dias_antes = $entre_dias_antes;
                  $this->entre_dias_despues = $entre_dias_despues;
                  $this->entre_turnos_antes = $entre_turnos_antes;
                  $this->entre_turnos_despues = $entre_turnos_despues;
                  $this->entre_tiempo_antes = $entre_tiempo_antes;
                  $this->entre_tiempo_despues = $entre_tiempo_despues;
                  $this->cant_exa = $cant_exa;
                  $this->cant_min = $cant_min;
                  $this->cant_max = $cant_max;
                  $this->consec_brigada = $consec_brigada;
                  $this->consec_profesor = $consec_profesor;
                  $this->consec_lugar = $consec_lugar;
                  $this->consec_otro_recurso = $consec_otro_recurso;
                  $this->consec_periodo = $consec_periodo;
                  $this->consec_dia_ini = $consec_dia_ini;
                  $this->consec_dia_fin = $consec_dia_fin;
                  $this->consec_turno_ini = $consec_turno_ini;
                  $this->consec_turno_fin = $consec_turno_fin;
                  $this->consec_discreto = $consec_discreto;
                  $this->siempre = $siempre;
                  $this->valor = $valor;
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
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET planificacion = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
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
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET activa = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
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
            * @name: setClasifAct 
            * @desc: Set value of property "ClasifAct" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setClasifAct($value){ 
              $this->clasif_act = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET clasif_act = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getClasifAct 
            * @desc: Get value of property "ClasifAct" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getClasifAct(){ 
              $r = $this->data->get($this->id); 
              $this->clasif_act = $r->clasif_act; 
              return $this->clasif_act;
       } 
          /** 
            * @name: setAsig 
            * @desc: Set value of property "Asig" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setAsig($value){ 
              $this->asig = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET asig = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getAsig 
            * @desc: Get value of property "Asig" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getAsig(){ 
              $r = $this->data->get($this->id); 
              $this->asig = $r->asig; 
              return $this->asig;
       } 
          /** 
            * @name: setBrigada 
            * @desc: Set value of property "Brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setBrigada($value){ 
              $this->brigada = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET brigada = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getBrigada 
            * @desc: Get value of property "Brigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getBrigada(){ 
              $r = $this->data->get($this->id); 
              $this->brigada = $r->brigada; 
              return $this->brigada;
       } 
          /** 
            * @name: setProfesor 
            * @desc: Set value of property "Profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setProfesor($value){ 
              $this->profesor = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET profesor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getProfesor 
            * @desc: Get value of property "Profesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->profesor = $r->profesor; 
              return $this->profesor;
       } 
          /** 
            * @name: setLugar 
            * @desc: Set value of property "Lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setLugar($value){ 
              $this->lugar = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET lugar = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getLugar 
            * @desc: Get value of property "Lugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getLugar(){ 
              $r = $this->data->get($this->id); 
              $this->lugar = $r->lugar; 
              return $this->lugar;
       } 
          /** 
            * @name: setOtroRecurso 
            * @desc: Set value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setOtroRecurso($value){ 
              $this->otro_recurso = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET otro_recurso = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getOtroRecurso 
            * @desc: Get value of property "OtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getOtroRecurso(){ 
              $r = $this->data->get($this->id); 
              $this->otro_recurso = $r->otro_recurso; 
              return $this->otro_recurso;
       } 
          /** 
            * @name: setEspecialidad 
            * @desc: Set value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEspecialidad($value){ 
              $this->especialidad = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET especialidad = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEspecialidad 
            * @desc: Get value of property "Especialidad" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEspecialidad(){ 
              $r = $this->data->get($this->id); 
              $this->especialidad = $r->especialidad; 
              return $this->especialidad;
       } 
          /** 
            * @name: setNivel 
            * @desc: Set value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setNivel($value){ 
              $this->nivel = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET nivel = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getNivel 
            * @desc: Get value of property "Nivel" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getNivel(){ 
              $r = $this->data->get($this->id); 
              $this->nivel = $r->nivel; 
              return $this->nivel;
       } 
          /** 
            * @name: setFechaIni 
            * @desc: Set value of property "FechaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setFechaIni($value){ 
              $this->fecha_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET fecha_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getFechaIni 
            * @desc: Get value of property "FechaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getFechaIni(){ 
              $r = $this->data->get($this->id); 
              $this->fecha_ini = $r->fecha_ini; 
              return $this->fecha_ini;
       } 
          /** 
            * @name: setFechaFin 
            * @desc: Set value of property "FechaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setFechaFin($value){ 
              $this->fecha_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET fecha_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getFechaFin 
            * @desc: Get value of property "FechaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getFechaFin(){ 
              $r = $this->data->get($this->id); 
              $this->fecha_fin = $r->fecha_fin; 
              return $this->fecha_fin;
       } 
          /** 
            * @name: setHoraIni 
            * @desc: Set value of property "HoraIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHoraIni($value){ 
              $this->hora_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET hora_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getHoraIni 
            * @desc: Get value of property "HoraIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHoraIni(){ 
              $r = $this->data->get($this->id); 
              $this->hora_ini = $r->hora_ini; 
              return $this->hora_ini;
       } 
          /** 
            * @name: setHoraFin 
            * @desc: Set value of property "HoraFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHoraFin($value){ 
              $this->hora_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET hora_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getHoraFin 
            * @desc: Get value of property "HoraFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHoraFin(){ 
              $r = $this->data->get($this->id); 
              $this->hora_fin = $r->hora_fin; 
              return $this->hora_fin;
       } 
          /** 
            * @name: setDiscretoExacto 
            * @desc: Set value of property "DiscretoExacto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiscretoExacto($value){ 
              $this->discreto_exacto = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET discreto_exacto = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiscretoExacto 
            * @desc: Get value of property "DiscretoExacto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiscretoExacto(){ 
              $r = $this->data->get($this->id); 
              $this->discreto_exacto = $r->discreto_exacto; 
              return $this->discreto_exacto;
       } 
          /** 
            * @name: setDiaIniPlanif 
            * @desc: Set value of property "DiaIniPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaIniPlanif($value){ 
              $this->dia_ini_planif = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_ini_planif = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaIniPlanif 
            * @desc: Get value of property "DiaIniPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaIniPlanif(){ 
              $r = $this->data->get($this->id); 
              $this->dia_ini_planif = $r->dia_ini_planif; 
              return $this->dia_ini_planif;
       } 
          /** 
            * @name: setDiaFinPlanif 
            * @desc: Set value of property "DiaFinPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaFinPlanif($value){ 
              $this->dia_fin_planif = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_fin_planif = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaFinPlanif 
            * @desc: Get value of property "DiaFinPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaFinPlanif(){ 
              $r = $this->data->get($this->id); 
              $this->dia_fin_planif = $r->dia_fin_planif; 
              return $this->dia_fin_planif;
       } 
          /** 
            * @name: setHoraIniPlanif 
            * @desc: Set value of property "HoraIniPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHoraIniPlanif($value){ 
              $this->hora_ini_planif = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET hora_ini_planif = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getHoraIniPlanif 
            * @desc: Get value of property "HoraIniPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHoraIniPlanif(){ 
              $r = $this->data->get($this->id); 
              $this->hora_ini_planif = $r->hora_ini_planif; 
              return $this->hora_ini_planif;
       } 
          /** 
            * @name: setHoraFinPlanif 
            * @desc: Set value of property "HoraFinPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHoraFinPlanif($value){ 
              $this->hora_fin_planif = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET hora_fin_planif = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getHoraFinPlanif 
            * @desc: Get value of property "HoraFinPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHoraFinPlanif(){ 
              $r = $this->data->get($this->id); 
              $this->hora_fin_planif = $r->hora_fin_planif; 
              return $this->hora_fin_planif;
       } 
          /** 
            * @name: setDiscretoExactoPlanif 
            * @desc: Set value of property "DiscretoExactoPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiscretoExactoPlanif($value){ 
              $this->discreto_exacto_planif = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET discreto_exacto_planif = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiscretoExactoPlanif 
            * @desc: Get value of property "DiscretoExactoPlanif" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiscretoExactoPlanif(){ 
              $r = $this->data->get($this->id); 
              $this->discreto_exacto_planif = $r->discreto_exacto_planif; 
              return $this->discreto_exacto_planif;
       } 
          /** 
            * @name: setPeriodo 
            * @desc: Set value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setPeriodo($value){ 
              $this->periodo = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET periodo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getPeriodo 
            * @desc: Get value of property "Periodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getPeriodo(){ 
              $r = $this->data->get($this->id); 
              $this->periodo = $r->periodo; 
              return $this->periodo;
       } 
          /** 
            * @name: setDiaIni 
            * @desc: Set value of property "DiaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaIni($value){ 
              $this->dia_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaIni 
            * @desc: Get value of property "DiaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaIni(){ 
              $r = $this->data->get($this->id); 
              $this->dia_ini = $r->dia_ini; 
              return $this->dia_ini;
       } 
          /** 
            * @name: setDiaFin 
            * @desc: Set value of property "DiaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaFin($value){ 
              $this->dia_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaFin 
            * @desc: Get value of property "DiaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaFin(){ 
              $r = $this->data->get($this->id); 
              $this->dia_fin = $r->dia_fin; 
              return $this->dia_fin;
       } 
          /** 
            * @name: setTurnoIni 
            * @desc: Set value of property "TurnoIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurnoIni($value){ 
              $this->turno_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET turno_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getTurnoIni 
            * @desc: Get value of property "TurnoIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurnoIni(){ 
              $r = $this->data->get($this->id); 
              $this->turno_ini = $r->turno_ini; 
              return $this->turno_ini;
       } 
          /** 
            * @name: setTurnoFin 
            * @desc: Set value of property "TurnoFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurnoFin($value){ 
              $this->turno_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET turno_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getTurnoFin 
            * @desc: Get value of property "TurnoFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurnoFin(){ 
              $r = $this->data->get($this->id); 
              $this->turno_fin = $r->turno_fin; 
              return $this->turno_fin;
       } 
          /** 
            * @name: setDiscreto 
            * @desc: Set value of property "Discreto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiscreto($value){ 
              $this->discreto = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET discreto = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiscreto 
            * @desc: Get value of property "Discreto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiscreto(){ 
              $r = $this->data->get($this->id); 
              $this->discreto = $r->discreto; 
              return $this->discreto;
       } 
          /** 
            * @name: setHoy 
            * @desc: Set value of property "Hoy" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setHoy($value){ 
              $this->hoy = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET hoy = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getHoy 
            * @desc: Get value of property "Hoy" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getHoy(){ 
              $r = $this->data->get($this->id); 
              $this->hoy = $r->hoy; 
              return $this->hoy;
       } 
          /** 
            * @name: setAhora 
            * @desc: Set value of property "Ahora" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setAhora($value){ 
              $this->ahora = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET ahora = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getAhora 
            * @desc: Get value of property "Ahora" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getAhora(){ 
              $r = $this->data->get($this->id); 
              $this->ahora = $r->ahora; 
              return $this->ahora;
       } 
          /** 
            * @name: setDiaRelativoAnterior 
            * @desc: Set value of property "DiaRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaRelativoAnterior($value){ 
              $this->dia_relativo_anterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_relativo_anterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaRelativoAnterior 
            * @desc: Get value of property "DiaRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaRelativoAnterior(){ 
              $r = $this->data->get($this->id); 
              $this->dia_relativo_anterior = $r->dia_relativo_anterior; 
              return $this->dia_relativo_anterior;
       } 
          /** 
            * @name: setCantDiasRelativoAnterior 
            * @desc: Set value of property "CantDiasRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantDiasRelativoAnterior($value){ 
              $this->cant_dias_relativo_anterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_dias_relativo_anterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantDiasRelativoAnterior 
            * @desc: Get value of property "CantDiasRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantDiasRelativoAnterior(){ 
              $r = $this->data->get($this->id); 
              $this->cant_dias_relativo_anterior = $r->cant_dias_relativo_anterior; 
              return $this->cant_dias_relativo_anterior;
       } 
          /** 
            * @name: setDiaRelativoPosterior 
            * @desc: Set value of property "DiaRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setDiaRelativoPosterior($value){ 
              $this->dia_relativo_posterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET dia_relativo_posterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getDiaRelativoPosterior 
            * @desc: Get value of property "DiaRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getDiaRelativoPosterior(){ 
              $r = $this->data->get($this->id); 
              $this->dia_relativo_posterior = $r->dia_relativo_posterior; 
              return $this->dia_relativo_posterior;
       } 
          /** 
            * @name: setCantDiasRelativoPosterior 
            * @desc: Set value of property "CantDiasRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantDiasRelativoPosterior($value){ 
              $this->cant_dias_relativo_posterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_dias_relativo_posterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantDiasRelativoPosterior 
            * @desc: Get value of property "CantDiasRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantDiasRelativoPosterior(){ 
              $r = $this->data->get($this->id); 
              $this->cant_dias_relativo_posterior = $r->cant_dias_relativo_posterior; 
              return $this->cant_dias_relativo_posterior;
       } 
          /** 
            * @name: setRangoTiempoRelativoAnteriorIni 
            * @desc: Set value of property "RangoTiempoRelativoAnteriorIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setRangoTiempoRelativoAnteriorIni($value){ 
              $this->rango_tiempo_relativo_anterior_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET rango_tiempo_relativo_anterior_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getRangoTiempoRelativoAnteriorIni 
            * @desc: Get value of property "RangoTiempoRelativoAnteriorIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getRangoTiempoRelativoAnteriorIni(){ 
              $r = $this->data->get($this->id); 
              $this->rango_tiempo_relativo_anterior_ini = $r->rango_tiempo_relativo_anterior_ini; 
              return $this->rango_tiempo_relativo_anterior_ini;
       } 
          /** 
            * @name: setRangoTiempoRelativoAnteriorTamano 
            * @desc: Set value of property "RangoTiempoRelativoAnteriorTamano" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setRangoTiempoRelativoAnteriorTamano($value){ 
              $this->rango_tiempo_relativo_anterior_tamano = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET rango_tiempo_relativo_anterior_tamano = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getRangoTiempoRelativoAnteriorTamano 
            * @desc: Get value of property "RangoTiempoRelativoAnteriorTamano" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getRangoTiempoRelativoAnteriorTamano(){ 
              $r = $this->data->get($this->id); 
              $this->rango_tiempo_relativo_anterior_tamano = $r->rango_tiempo_relativo_anterior_tamano; 
              return $this->rango_tiempo_relativo_anterior_tamano;
       } 
          /** 
            * @name: setTurnoRelativoAnterior 
            * @desc: Set value of property "TurnoRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurnoRelativoAnterior($value){ 
              $this->turno_relativo_anterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET turno_relativo_anterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getTurnoRelativoAnterior 
            * @desc: Get value of property "TurnoRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurnoRelativoAnterior(){ 
              $r = $this->data->get($this->id); 
              $this->turno_relativo_anterior = $r->turno_relativo_anterior; 
              return $this->turno_relativo_anterior;
       } 
          /** 
            * @name: setCantTurnosRelativoAnterior 
            * @desc: Set value of property "CantTurnosRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantTurnosRelativoAnterior($value){ 
              $this->cant_turnos_relativo_anterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_turnos_relativo_anterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantTurnosRelativoAnterior 
            * @desc: Get value of property "CantTurnosRelativoAnterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantTurnosRelativoAnterior(){ 
              $r = $this->data->get($this->id); 
              $this->cant_turnos_relativo_anterior = $r->cant_turnos_relativo_anterior; 
              return $this->cant_turnos_relativo_anterior;
       } 
          /** 
            * @name: setTurnoRelativoPosterior 
            * @desc: Set value of property "TurnoRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setTurnoRelativoPosterior($value){ 
              $this->turno_relativo_posterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET turno_relativo_posterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getTurnoRelativoPosterior 
            * @desc: Get value of property "TurnoRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getTurnoRelativoPosterior(){ 
              $r = $this->data->get($this->id); 
              $this->turno_relativo_posterior = $r->turno_relativo_posterior; 
              return $this->turno_relativo_posterior;
       } 
          /** 
            * @name: setCantTurnosRelativoPosterior 
            * @desc: Set value of property "CantTurnosRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantTurnosRelativoPosterior($value){ 
              $this->cant_turnos_relativo_posterior = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_turnos_relativo_posterior = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantTurnosRelativoPosterior 
            * @desc: Get value of property "CantTurnosRelativoPosterior" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantTurnosRelativoPosterior(){ 
              $r = $this->data->get($this->id); 
              $this->cant_turnos_relativo_posterior = $r->cant_turnos_relativo_posterior; 
              return $this->cant_turnos_relativo_posterior;
       } 
          /** 
            * @name: setRangoTiempoRelativoPosteriorIni 
            * @desc: Set value of property "RangoTiempoRelativoPosteriorIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setRangoTiempoRelativoPosteriorIni($value){ 
              $this->rango_tiempo_relativo_posterior_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET rango_tiempo_relativo_posterior_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getRangoTiempoRelativoPosteriorIni 
            * @desc: Get value of property "RangoTiempoRelativoPosteriorIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getRangoTiempoRelativoPosteriorIni(){ 
              $r = $this->data->get($this->id); 
              $this->rango_tiempo_relativo_posterior_ini = $r->rango_tiempo_relativo_posterior_ini; 
              return $this->rango_tiempo_relativo_posterior_ini;
       } 
          /** 
            * @name: setRangoTiempoRelativoPosteriorTamano 
            * @desc: Set value of property "RangoTiempoRelativoPosteriorTamano" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setRangoTiempoRelativoPosteriorTamano($value){ 
              $this->rango_tiempo_relativo_posterior_tamano = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET rango_tiempo_relativo_posterior_tamano = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getRangoTiempoRelativoPosteriorTamano 
            * @desc: Get value of property "RangoTiempoRelativoPosteriorTamano" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getRangoTiempoRelativoPosteriorTamano(){ 
              $r = $this->data->get($this->id); 
              $this->rango_tiempo_relativo_posterior_tamano = $r->rango_tiempo_relativo_posterior_tamano; 
              return $this->rango_tiempo_relativo_posterior_tamano;
       } 
          /** 
            * @name: setEntreDiasAntes 
            * @desc: Set value of property "EntreDiasAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreDiasAntes($value){ 
              $this->entre_dias_antes = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_dias_antes = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreDiasAntes 
            * @desc: Get value of property "EntreDiasAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreDiasAntes(){ 
              $r = $this->data->get($this->id); 
              $this->entre_dias_antes = $r->entre_dias_antes; 
              return $this->entre_dias_antes;
       } 
          /** 
            * @name: setEntreDiasDespues 
            * @desc: Set value of property "EntreDiasDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreDiasDespues($value){ 
              $this->entre_dias_despues = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_dias_despues = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreDiasDespues 
            * @desc: Get value of property "EntreDiasDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreDiasDespues(){ 
              $r = $this->data->get($this->id); 
              $this->entre_dias_despues = $r->entre_dias_despues; 
              return $this->entre_dias_despues;
       } 
          /** 
            * @name: setEntreTurnosAntes 
            * @desc: Set value of property "EntreTurnosAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreTurnosAntes($value){ 
              $this->entre_turnos_antes = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_turnos_antes = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreTurnosAntes 
            * @desc: Get value of property "EntreTurnosAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreTurnosAntes(){ 
              $r = $this->data->get($this->id); 
              $this->entre_turnos_antes = $r->entre_turnos_antes; 
              return $this->entre_turnos_antes;
       } 
          /** 
            * @name: setEntreTurnosDespues 
            * @desc: Set value of property "EntreTurnosDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreTurnosDespues($value){ 
              $this->entre_turnos_despues = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_turnos_despues = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreTurnosDespues 
            * @desc: Get value of property "EntreTurnosDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreTurnosDespues(){ 
              $r = $this->data->get($this->id); 
              $this->entre_turnos_despues = $r->entre_turnos_despues; 
              return $this->entre_turnos_despues;
       } 
          /** 
            * @name: setEntreTiempoAntes 
            * @desc: Set value of property "EntreTiempoAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreTiempoAntes($value){ 
              $this->entre_tiempo_antes = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_tiempo_antes = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreTiempoAntes 
            * @desc: Get value of property "EntreTiempoAntes" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreTiempoAntes(){ 
              $r = $this->data->get($this->id); 
              $this->entre_tiempo_antes = $r->entre_tiempo_antes; 
              return $this->entre_tiempo_antes;
       } 
          /** 
            * @name: setEntreTiempoDespues 
            * @desc: Set value of property "EntreTiempoDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setEntreTiempoDespues($value){ 
              $this->entre_tiempo_despues = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET entre_tiempo_despues = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getEntreTiempoDespues 
            * @desc: Get value of property "EntreTiempoDespues" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getEntreTiempoDespues(){ 
              $r = $this->data->get($this->id); 
              $this->entre_tiempo_despues = $r->entre_tiempo_despues; 
              return $this->entre_tiempo_despues;
       } 
          /** 
            * @name: setCantExa 
            * @desc: Set value of property "CantExa" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantExa($value){ 
              $this->cant_exa = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_exa = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantExa 
            * @desc: Get value of property "CantExa" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantExa(){ 
              $r = $this->data->get($this->id); 
              $this->cant_exa = $r->cant_exa; 
              return $this->cant_exa;
       } 
          /** 
            * @name: setCantMin 
            * @desc: Set value of property "CantMin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantMin($value){ 
              $this->cant_min = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_min = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantMin 
            * @desc: Get value of property "CantMin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantMin(){ 
              $r = $this->data->get($this->id); 
              $this->cant_min = $r->cant_min; 
              return $this->cant_min;
       } 
          /** 
            * @name: setCantMax 
            * @desc: Set value of property "CantMax" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setCantMax($value){ 
              $this->cant_max = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET cant_max = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getCantMax 
            * @desc: Get value of property "CantMax" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getCantMax(){ 
              $r = $this->data->get($this->id); 
              $this->cant_max = $r->cant_max; 
              return $this->cant_max;
       } 
          /** 
            * @name: setConsecBrigada 
            * @desc: Set value of property "ConsecBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecBrigada($value){ 
              $this->consec_brigada = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_brigada = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecBrigada 
            * @desc: Get value of property "ConsecBrigada" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecBrigada(){ 
              $r = $this->data->get($this->id); 
              $this->consec_brigada = $r->consec_brigada; 
              return $this->consec_brigada;
       } 
          /** 
            * @name: setConsecProfesor 
            * @desc: Set value of property "ConsecProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecProfesor($value){ 
              $this->consec_profesor = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_profesor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecProfesor 
            * @desc: Get value of property "ConsecProfesor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecProfesor(){ 
              $r = $this->data->get($this->id); 
              $this->consec_profesor = $r->consec_profesor; 
              return $this->consec_profesor;
       } 
          /** 
            * @name: setConsecLugar 
            * @desc: Set value of property "ConsecLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecLugar($value){ 
              $this->consec_lugar = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_lugar = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecLugar 
            * @desc: Get value of property "ConsecLugar" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecLugar(){ 
              $r = $this->data->get($this->id); 
              $this->consec_lugar = $r->consec_lugar; 
              return $this->consec_lugar;
       } 
          /** 
            * @name: setConsecOtroRecurso 
            * @desc: Set value of property "ConsecOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecOtroRecurso($value){ 
              $this->consec_otro_recurso = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_otro_recurso = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecOtroRecurso 
            * @desc: Get value of property "ConsecOtroRecurso" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecOtroRecurso(){ 
              $r = $this->data->get($this->id); 
              $this->consec_otro_recurso = $r->consec_otro_recurso; 
              return $this->consec_otro_recurso;
       } 
          /** 
            * @name: setConsecPeriodo 
            * @desc: Set value of property "ConsecPeriodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecPeriodo($value){ 
              $this->consec_periodo = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_periodo = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "text"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecPeriodo 
            * @desc: Get value of property "ConsecPeriodo" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecPeriodo(){ 
              $r = $this->data->get($this->id); 
              $this->consec_periodo = $r->consec_periodo; 
              return $this->consec_periodo;
       } 
          /** 
            * @name: setConsecDiaIni 
            * @desc: Set value of property "ConsecDiaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecDiaIni($value){ 
              $this->consec_dia_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_dia_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecDiaIni 
            * @desc: Get value of property "ConsecDiaIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecDiaIni(){ 
              $r = $this->data->get($this->id); 
              $this->consec_dia_ini = $r->consec_dia_ini; 
              return $this->consec_dia_ini;
       } 
          /** 
            * @name: setConsecDiaFin 
            * @desc: Set value of property "ConsecDiaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecDiaFin($value){ 
              $this->consec_dia_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_dia_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecDiaFin 
            * @desc: Get value of property "ConsecDiaFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecDiaFin(){ 
              $r = $this->data->get($this->id); 
              $this->consec_dia_fin = $r->consec_dia_fin; 
              return $this->consec_dia_fin;
       } 
          /** 
            * @name: setConsecTurnoIni 
            * @desc: Set value of property "ConsecTurnoIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecTurnoIni($value){ 
              $this->consec_turno_ini = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_turno_ini = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecTurnoIni 
            * @desc: Get value of property "ConsecTurnoIni" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecTurnoIni(){ 
              $r = $this->data->get($this->id); 
              $this->consec_turno_ini = $r->consec_turno_ini; 
              return $this->consec_turno_ini;
       } 
          /** 
            * @name: setConsecTurnoFin 
            * @desc: Set value of property "ConsecTurnoFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecTurnoFin($value){ 
              $this->consec_turno_fin = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_turno_fin = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecTurnoFin 
            * @desc: Get value of property "ConsecTurnoFin" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecTurnoFin(){ 
              $r = $this->data->get($this->id); 
              $this->consec_turno_fin = $r->consec_turno_fin; 
              return $this->consec_turno_fin;
       } 
          /** 
            * @name: setConsecDiscreto 
            * @desc: Set value of property "ConsecDiscreto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setConsecDiscreto($value){ 
              $this->consec_discreto = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET consec_discreto = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getConsecDiscreto 
            * @desc: Get value of property "ConsecDiscreto" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getConsecDiscreto(){ 
              $r = $this->data->get($this->id); 
              $this->consec_discreto = $r->consec_discreto; 
              return $this->consec_discreto;
       } 
          /** 
            * @name: setSiempre 
            * @desc: Set value of property "Siempre" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setSiempre($value){ 
              $this->siempre = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET siempre = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "bool"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getSiempre 
            * @desc: Get value of property "Siempre" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getSiempre(){ 
              $r = $this->data->get($this->id); 
              $this->siempre = $r->siempre; 
              return $this->siempre;
       } 
          /** 
            * @name: setValor 
            * @desc: Set value of property "Valor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function setValor($value){ 
              $this->valor = $value; 
              $this->data->getAnythingParams("UPDATE restriccion_condicional SET valor = $1 WHERE id = $2;", array(PartModel::sqlValue($value, "number"), PartModel::sqlValue($this->id, "text"))); 
       } 
          /** 
            * @name: getValor 
            * @desc: Get value of property "Valor" 
            * @updated: 20/06/10 10:40:54 
            * @version: 1.0 
            */ 
       public function getValor(){ 
              $r = $this->data->get($this->id); 
              $this->valor = $r->valor; 
              return $this->valor;
       } 
} 
?>