<?php 
    /** 
      * @name: publicFunctions 
      * @desc: Functions of schema public 
      * @updated: 20/06/10 10:40:56 
      * @version: 1.0 
      */ 
     class PublicFunctions extends PartModel{ 
        var $db; 
          /** 
            * @name: publicFunctions 
            * @desc: Constructor 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function __construct($db){ 
               $this->db = $db;  
          }
          /** 
            * @name: dame_asig_segun_esp_anno_per 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsigSegunEspAnnoPer($idesp,$idnivel,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_asig_segun_esp_anno_per($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_asignatura_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsignaturaDesglose($param1,$asobjects = false){ 
               $sql = "select  public.dame_asignatura_desglose($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_asignatura_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsignaturaPlantillaDeActividad($id,$asobjects = false){ 
               $sql = "select  public.dame_asignatura_plantilla_de_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: dame_brigadas_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameBrigadasDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select * from public.dame_brigadas_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_brigadas_sin_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameBrigadasSinGrupo($idesp,$idnivel,$idclasif,$idplanificacion,$asobjects = false){ 
               $sql = "select  public.dame_brigadas_sin_grupo($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idclasif, "text"), PartModel::sqlValue($idplanificacion, "text")), $asobjects);
           } 
          /** 
            * @name: busca_lugar_menos_costoso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMenosCostoso($origenes,$destinos,$asobjects = false){ 
               $sql = "select  public.busca_lugar_menos_costoso($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($origenes, "text"), PartModel::sqlValue($destinos, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_act_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantActDesglose($desglose,$asobjects = false){ 
               $sql = "select  public.dame_cant_act_desglose($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($desglose, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_asig_de_esp_anno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAsigDeEspAnno($idesp,$idnivel,$asobjects = false){ 
               $sql = "select  public.dame_cant_asig_de_esp_anno($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantClasif($asobjects = false){ 
               $sql = "select  public.dame_cant_clasif()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: dame_cant_dias 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantDias($idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_dias($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_grupos_de_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantGruposDeRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_grupos_de_recursos_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_lugares_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantLugaresAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_lugares_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantOtrosRecursosAsignados($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_cant_otros_recursos_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_profes_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantProfesAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_profes_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_rest_profe 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantRestProfe($idprofe,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_rest_profe($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idprofe, "text"), PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_turnos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantTurnos($idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_turnos($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_capacidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCapacidad($lug,$asobjects = false){ 
               $sql = "select  public.dame_capacidad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($lug, "text")), $asobjects);
           } 
          /** 
            * @name: dame_clasif_de_asignaturas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasifDeAsignaturas($asignatura,$periodo,$asobjects = false){ 
               $sql = "select  public.dame_clasif_de_asignaturas($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($asignatura, "text"), PartModel::sqlValue($periodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_clasif_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasifPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_clasif_plantilla_de_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_clasificacion_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasificacionDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_clasificacion_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_costo_trasladar_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCostoTrasladarRecurso($idrecurso,$idlugar,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_costo_trasladar_recurso($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idrecurso, "text"), PartModel::sqlValue($idlugar, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: dame_creador_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_asignatura($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idasig, "text")), $asobjects);
           } 
          /** 
            * @name: dame_creador_de_clasifact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeClasifact($idclasif,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_clasifact($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idclasif, "text")), $asobjects);
           } 
          /** 
            * @name: dame_creador_de_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeEspecialidad($esp,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_especialidad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($esp, "text")), $asobjects);
           } 
          /** 
            * @name: dame_creador_de_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDePlanificacion($id,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_planificacion($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: dame_creador_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_otros_recursos_imprescindibles 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantOtrosRecursosImprescindibles($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_otros_recursos_imprescindibles($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_desglose_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDesgloseDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_desglose_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_dia_real_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDiaRealPlanificacion($pid,$dia,$asobjects = false){ 
               $sql = "select  public.dame_dia_real_planificacion($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text"), PartModel::sqlValue($dia, "number")), $asobjects);
           } 
          /** 
            * @name: dame_duracion_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionActividad($id,$asobjects = false){ 
               $sql = "select  public.dame_duracion_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: dame_duracion_clasif_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionClasifDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_duracion_clasif_de_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_duracion_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_duracion_plantilla_de_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_duracion_turno_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionTurnoPeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_duracion_turno_periodo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_especialidad_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_asignatura($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idasig, "text")), $asobjects);
           } 
          /** 
            * @name: dame_especialidad_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeBrigada($idbrigada,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_brigada($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idbrigada, "text")), $asobjects);
           } 
          /** 
            * @name: dame_especialidad_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_gasto_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGastoDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_gasto_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_gasto_lugares_en_el_dia 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGastoLugaresEnElDia($lugares,$periodo,$pdia,$asobjects = false){ 
               $sql = "select  public.dame_gasto_lugares_en_el_dia($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($lugares, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($pdia, "number")), $asobjects);
           } 
          /** 
            * @name: dame_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGrupo($brg,$clasif,$asobjects = false){ 
               $sql = "select  public.dame_grupo($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brg, "text"), PartModel::sqlValue($clasif, "text")), $asobjects);
           } 
          /** 
            * @name: dame_grupos_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGruposDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_grupos_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_hora_inicial_turno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameHoraInicialTurno($idperiodo,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_hora_inicial_turno($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: dame_id_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdPlantilla($idasig,$idperiodo,$orden,$asobjects = false){ 
               $sql = "select  public.dame_id_plantilla($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idasig, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($orden, "number")), $asobjects);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDeBrigada($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_brigada($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDeEspecialidad($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_especialidad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDePeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_periodo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_brigadas_de_esp 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdBrigadasDeEsp($esp,$niv,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_brigadas_de_esp($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($esp, "text"), PartModel::sqlValue($niv, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_brigadas_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdBrigadasDeGrupo($pgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_brigadas_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdClasif($asobjects = false){ 
               $sql = "select  public.dame_lista_id_clasif()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_grupos_de_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdGruposDeRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_grupos_de_recursos_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_lugares_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdLugaresAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_lugares_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdOtrosRecursosAsignados($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_otros_recursos_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_profes_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdProfesAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_profes_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lugares_no_asignados_a 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresNoAsignadosA($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_lugares_no_asignados_a($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lugares_utilizados_en_el_dia 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresUtilizadosEnElDia($pgrupo,$periodo,$pdia,$asobjects = false){ 
               $sql = "select  public.dame_lugares_utilizados_en_el_dia($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pgrupo, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($pdia, "number")), $asobjects);
           } 
          /** 
            * @name: dame_matricula 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMatricula($brg,$asobjects = false){ 
               $sql = "select  public.dame_matricula($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brg, "text")), $asobjects);
           } 
          /** 
            * @name: dame_matricula_total 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMatriculaTotal($brigadas,$asobjects = false){ 
               $sql = "select  public.dame_matricula_total($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brigadas, "text")), $asobjects);
           } 
          /** 
            * @name: dame_max_orden_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxOrdenAct($iddesglose,$asobjects = false){ 
               $sql = "select  public.dame_max_orden_act($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($iddesglose, "text")), $asobjects);
           } 
          /** 
            * @name: if 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function goIf($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.if($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "bool"), PartModel::sqlValue($param2, "number"), PartModel::sqlValue($param3, "number")), $asobjects);
           } 
          /** 
            * @name: dame_max_priori_clasif_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioriClasifAct($pcreador,$asobjects = false){ 
               $sql = "select  public.dame_max_priori_clasif_act($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pcreador, "text")), $asobjects);
           } 
          /** 
            * @name: dame_max_prioridad_de_pxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadDePxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_de_pxact($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_max_prioridad_lxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadLxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_lxact($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_max_prioridad_rxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadRxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_rxact($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_nivel_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_asignatura($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idasig, "text")), $asobjects);
           } 
          /** 
            * @name: dame_nivel_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeBrigada($idbrigada,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_brigada($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idbrigada, "text")), $asobjects);
           } 
          /** 
            * @name: dame_nivel_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_periodo_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePeriodoDesglose($param1,$asobjects = false){ 
               $sql = "select  public.dame_periodo_desglose($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_periodo_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePeriodoPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_periodo_plantilla_de_actividad($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_planificacion_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlanificacionDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_planificacion_de_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_planificacion_de_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlanificacionDePeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_planificacion_de_periodo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_profesores_no_asignados_a 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameProfesoresNoAsignadosA($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_profesores_no_asignados_a($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_tipo_consec_rest_cond 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoConsecRestCond($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_consec_rest_cond($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_tipo_hijo_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoHijoHrt($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_hijo_hrt($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_tipo_padre_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoPadreHrt($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_padre_hrt($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pid, "text")), $asobjects);
           } 
          /** 
            * @name: dame_uso_de_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameUsoDeOtroRecurso($id,$asobjects = false){ 
               $sql = "select  public.dame_uso_de_otro_recurso($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: dias_se_solapan 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DiasSeSolapan($idperiodo1,$dia1,$idperiodo2,$dia2,$asobjects = false){ 
               $sql = "select  public.dias_se_solapan($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo1, "text"), PartModel::sqlValue($dia1, "number"), PartModel::sqlValue($idperiodo2, "text"), PartModel::sqlValue($dia2, "number")), $asobjects);
           } 
          /** 
            * @name: es_lugar_prohibido 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsLugarProhibido($pgrupo,$plugar,$asobjects = false){ 
               $sql = "select  public.es_lugar_prohibido($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pgrupo, "text"), PartModel::sqlValue($plugar, "text")), $asobjects);
           } 
          /** 
            * @name: esta_dia_en_rango_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EstaDiaEnRangoPlanificacion($pdia,$idperiodo,$dia_ini,$dia_fin,$asobjects = false){ 
               $sql = "select  public.esta_dia_en_rango_planificacion($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($dia_ini, "number"), PartModel::sqlValue($dia_fin, "number")), $asobjects);
           } 
          /** 
            * @name: existe_grupo_de_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExisteGrupoDeProfesor($profe_para_act,$pgrupo,$asobjects = false){ 
               $sql = "select  public.existe_grupo_de_profesor($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($profe_para_act, "text"), PartModel::sqlValue($pgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: first 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function First($param1,$param2,$asobjects = false){ 
               $sql = "select  public.first($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text"), PartModel::sqlValue($param2, "text")), $asobjects);
           } 
          /** 
            * @name: greatest 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Greatest($param1,$param2,$asobjects = false){ 
               $sql = "select  public.greatest($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text"), PartModel::sqlValue($param2, "text")), $asobjects);
           } 
          /** 
            * @name: greatest 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Greatest_v2($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.greatest($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text"), PartModel::sqlValue($param2, "text"), PartModel::sqlValue($param3, "text")), $asobjects);
           } 
          /** 
            * @name: if 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function goIf_v2($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.if($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "bool"), PartModel::sqlValue($param2, "text"), PartModel::sqlValue($param3, "text")), $asobjects);
           } 
          /** 
            * @name: es_higienico_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsHigienicoProfesor($idprofesor,$idplantilla,$pdia,$asobjects = false){ 
               $sql = "select  public.es_higienico_profesor($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idprofesor, "text"), PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($pdia, "number")), $asobjects);
           } 
          /** 
            * @name: extraer_lugares_que_no_permanecen 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExtraerLugaresQueNoPermanecen($lugares,$periodo,$dia,$turno,$duracion,$asobjects = false){ 
               $sql = "select  public.extraer_lugares_que_no_permanecen($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($lugares, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($duracion, "number")), $asobjects);
           } 
          /** 
            * @name: es_higienico_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsHigienicoBrigada($idbrigada,$idplantilla,$pdia,$asobjects = false){ 
               $sql = "select  public.es_higienico_brigada($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idbrigada, "text"), PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($pdia, "number")), $asobjects);
           } 
          /** 
            * @name: iif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Iif($expresion,$dato1,$dato2,$asobjects = false){ 
               $sql = "select  public.iif($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($expresion, "bool"), PartModel::sqlValue($dato1, "text"), PartModel::sqlValue($dato2, "text")), $asobjects);
           } 
          /** 
            * @name: max_cant_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function MaxCantAct($especialidad,$nivel,$periodo,$asobjects = false){ 
               $sql = "select  public.max_cant_act($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($especialidad, "text"), PartModel::sqlValue($nivel, "text"), PartModel::sqlValue($periodo, "text")), $asobjects);
           } 
          /** 
            * @name: mueve_o_crea_brigada_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function MueveOCreaBrigadaDeGrupo($idbrg,$idgrupo,$asobjects = false){ 
               $sql = "select  public.mueve_o_crea_brigada_de_grupo($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idbrg, "text"), PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: nivela_prioridad_lxact_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadLxactAfterDelete($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_lxact_after_delete()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: nivela_prioridad_lxact_after_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadLxactAfterUpdate($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_lxact_after_update()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: nivela_prioridad_rxact_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadRxactAfterDelete($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_rxact_after_delete()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: nuevo_desglose_cuando_nueva_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NuevoDesgloseCuandoNuevaAsignatura($asobjects = false){ 
               $sql = "select  public.nuevo_desglose_cuando_nueva_asignatura()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: nuevo_desglose_cuando_nuevo_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NuevoDesgloseCuandoNuevoPeriodo($asobjects = false){ 
               $sql = "select  public.nuevo_desglose_cuando_nuevo_periodo()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: plantilla_exige_lugar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlantillaExigeLugar($id,$asobjects = false){ 
               $sql = "select  public.plantilla_exige_lugar($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: plantilla_exige_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlantillaExigeProfesor($id,$asobjects = false){ 
               $sql = "select  public.plantilla_exige_profesor($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text")), $asobjects);
           } 
          /** 
            * @name: ocurre_causa_rest_cond 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function OcurreCausaRestCond($rc,$act,$idperiodo,$pdia,$pturno,$analiza_completo,$asobjects = false){ 
               $sql = "select  public.ocurre_causa_rest_cond($1,$2,$3,$4,$5,$6)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($rc, "text"), PartModel::sqlValue($act, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($analiza_completo, "bool")), $asobjects);
           } 
          /** 
            * @name: lugar_permanece_en 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function LugarPermaneceEn($plugar,$pperiodo,$pdia,$pturno,$duracion,$asobjects = false){ 
               $sql = "select  public.lugar_permanece_en($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($plugar, "text"), PartModel::sqlValue($pperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($duracion, "number")), $asobjects);
           } 
          /** 
            * @name: rand 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Rand($asobjects = false){ 
               $sql = "select  public.rand()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: restringe_por_uso_de_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function RestringePorUsoDeOtroRecurso($idactividad,$idrecurso,$asobjects = false){ 
               $sql = "select  public.restringe_por_uso_de_otro_recurso($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idactividad, "text"), PartModel::sqlValue($idrecurso, "text")), $asobjects);
           } 
          /** 
            * @name: sube_orden_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function SubeOrdenDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.sube_orden_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: turnos_se_solapan 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function TurnosSeSolapan($idperiodo1,$dia1,$turno1,$duracion1,$idperiodo2,$dia2,$turno2,$duracion2,$asobjects = false){ 
               $sql = "select  public.turnos_se_solapan($1,$2,$3,$4,$5,$6,$7,$8)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo1, "text"), PartModel::sqlValue($dia1, "number"), PartModel::sqlValue($turno1, "number"), PartModel::sqlValue($duracion1, "number"), PartModel::sqlValue($idperiodo2, "text"), PartModel::sqlValue($dia2, "number"), PartModel::sqlValue($turno2, "number"), PartModel::sqlValue($duracion2, "number")), $asobjects);
           } 
          /** 
            * @name: quita_segun_prohibidos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function QuitaSegunProhibidos($grupos,$lugares,$asobjects = false){ 
               $sql = "select  public.quita_segun_prohibidos($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($grupos, "text"), PartModel::sqlValue($lugares, "text")), $asobjects);
           } 
          /** 
            * @name: restringe_segun_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function RestringeSegunActividad($idactividad,$idplantilla,$idprofesor,$idlugar,$pdia,$pturno,$idgrupo,$asobjects = false){ 
               $sql = "select  public.restringe_segun_actividad($1,$2,$3,$4,$5,$6,$7)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idactividad, "text"), PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($idprofesor, "text"), PartModel::sqlValue($idlugar, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: rango 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Rango($idplantilla,$idgrupo,$minimo,$maximo,$asobjects = false){ 
               $sql = "select * from public.rango($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($idgrupo, "text"), PartModel::sqlValue($minimo, "number"), PartModel::sqlValue($maximo, "number")), $asobjects);
           } 
          /** 
            * @name: valor_rest_entre_config_distintas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestEntreConfigDistintas($rests,$per1,$per2,$dia,$turno,$asobjects = false){ 
               $sql = "select  public.valor_rest_entre_config_distintas($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($rests, "text"), PartModel::sqlValue($per1, "text"), PartModel::sqlValue($per2, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionAsignatura($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_asignatura($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionBrigada($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_brigada($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_clasif_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionClasifAct($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_clasif_act($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_condicional 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionCondicional($tipo_consec,$id_consec,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_condicional($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($tipo_consec, "text"), PartModel::sqlValue($id_consec, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionEspecialidad($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_especialidad($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_lugar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionLugar($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_lugar($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionPeriodo($idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_periodo($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionProfesor($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_profesor($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: valor_zpriori 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorZpriori($clasif,$periodo,$dia,$turno,$asobjects = false){ 
               $sql = "select  public.valor_zpriori($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($clasif, "text"), PartModel::sqlValue($periodo, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number")), $asobjects);
           } 
          /** 
            * @name: plpgsql_oid_debug 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlpgsqlOidDebug($functionoid,$asobjects = false){ 
               $sql = "select  public.plpgsql_oid_debug($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($functionoid, "text")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_proxy_info 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetProxyInfo($asobjects = false){ 
               $sql = "select * from public.pldbg_get_proxy_info()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: pldbg_wait_for_target 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgWaitForTarget($session,$asobjects = false){ 
               $sql = "select  public.pldbg_wait_for_target($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_act_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterDelete($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_delete()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_act_after_insert 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterInsert($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_insert()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_act_after_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterUpdate($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_update()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_act_before_insert 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActBeforeInsert($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_before_insert()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_act_before_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActBeforeUpdate($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_before_update()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_actividades 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActividades($asobjects = false){ 
               $sql = "select  public.actualiza_orden_actividades()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: actualiza_orden_plantillas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenPlantillas($iddesglose,$asobjects = false){ 
               $sql = "select  public.actualiza_orden_plantillas($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($iddesglose, "text")), $asobjects);
           } 
          /** 
            * @name: actualiza_profesor_para_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaProfesorParaGrupo($asobjects = false){ 
               $sql = "select  public.actualiza_profesor_para_grupo()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: baja_orden_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BajaOrdenDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.baja_orden_de_plantilla($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: bool_to_int 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BoolToInt($valor,$asobjects = false){ 
               $sql = "select  public.bool_to_int($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($valor, "bool")), $asobjects);
           } 
          /** 
            * @name: bool_to_str 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BoolToStr($valor,$asobjects = false){ 
               $sql = "select  public.bool_to_str($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($valor, "bool")), $asobjects);
           } 
          /** 
            * @name: dame_cant_accesos_prohibidos_al_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAccesosProhibidosAlGrupo($param1,$asobjects = false){ 
               $sql = "select  public.dame_cant_accesos_prohibidos_al_grupo($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_cant_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAct($asig,$periodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_act($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($asig, "text"), PartModel::sqlValue($periodo, "text")), $asobjects);
           } 
          /** 
            * @name: concat 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Concat($param1,$param2,$asobjects = false){ 
               $sql = "select  public.concat($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text"), PartModel::sqlValue($param2, "text")), $asobjects);
           } 
          /** 
            * @name: and_mpi 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function AndMpi($listaidbrg,$cd,$ct,$idplantilla,$zona,$excepto,$asobjects = false){ 
               $sql = "select  public.and_mpi($1,$2,$3,$4,$5,$6)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($listaidbrg, "text"), PartModel::sqlValue($cd, "number"), PartModel::sqlValue($ct, "number"), PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($zona, "number"), PartModel::sqlValue($excepto, "bool")), $asobjects);
           } 
          /** 
            * @name: busca_lugar_mas_utilizado_por 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMasUtilizadoPor($lugares,$idgrupo,$los_demas,$asobjects = false){ 
               $sql = "select  public.busca_lugar_mas_utilizado_por($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($lugares, "text"), PartModel::sqlValue($idgrupo, "text"), PartModel::sqlValue($los_demas, "bool")), $asobjects);
           } 
          /** 
            * @name: agrega_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function AgregaActividad($idplantilla,$idprofesor,$idlugar,$pdia,$pturno,$idgrupo,$idrecursos,$momento,$pcreador,$asobjects = false){ 
               $sql = "select  public.agrega_actividad($1,$2,$3,$4,$5,$6,$7,$8,$9)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($idprofesor, "text"), PartModel::sqlValue($idlugar, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($idgrupo, "text"), PartModel::sqlValue($idrecursos, "text"), PartModel::sqlValue($momento, "number"), PartModel::sqlValue($pcreador, "text")), $asobjects);
           } 
          /** 
            * @name: check_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CheckHrt($hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,$asobjects = false){ 
               $sql = "select  public.check_hrt($1,$2,$3,$4,$5,$6,$7,$8)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($hijo_brigada, "text"), PartModel::sqlValue($hijo_profesor, "text"), PartModel::sqlValue($hijo_lugar, "text"), PartModel::sqlValue($hijo_otro_recurso, "text"), PartModel::sqlValue($padre_brigada, "text"), PartModel::sqlValue($padre_profesor, "text"), PartModel::sqlValue($padre_lugar, "text"), PartModel::sqlValue($padre_otro_recurso, "text")), $asobjects);
           } 
          /** 
            * @name: crear_cadena_aleatoria 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CrearCadenaAleatoria($caracteres,$longitud,$asobjects = false){ 
               $sql = "select  public.crear_cadena_aleatoria($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($caracteres, "text"), PartModel::sqlValue($longitud, "number")), $asobjects);
           } 
          /** 
            * @name: crear_id_de_replica 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CrearIdDeReplica($prefijo,$tabla,$campoid,$sufijo,$asobjects = false){ 
               $sql = "select  public.crear_id_de_replica($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($prefijo, "text"), PartModel::sqlValue($tabla, "text"), PartModel::sqlValue($campoid, "text"), PartModel::sqlValue($sufijo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_asig_segun_esp_anno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsigSegunEspAnno($idesp,$idnivel,$asobjects = false){ 
               $sql = "select  public.dame_asig_segun_esp_anno($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_heredada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionHeredada($tipo_hijo,$id_hijo,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_heredada($1,$2,$3,$4,$5)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($tipo_hijo, "text"), PartModel::sqlValue($id_hijo, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: generar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Generar($idesp,$idnivel,$idperiodo,$forma,$parche,$compactar,$parche1,$buscalugar,$asobjects = false){ 
               $sql = "select  public.generar($1,$2,$3,$4,$5,$6,$7,$8)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($forma, "number"), PartModel::sqlValue($parche, "bool"), PartModel::sqlValue($compactar, "number"), PartModel::sqlValue($parche1, "bool"), PartModel::sqlValue($buscalugar, "bool")), $asobjects);
           } 
          /** 
            * @name: dame_cant_rest_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantRestAsignatura($idasig,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_rest_asignatura($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idasig, "text"), PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_target_info 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetTargetInfo($signature,$targettype,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_target_info($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($signature, "text"), PartModel::sqlValue($targettype, "text")), $asobjects);
           } 
          /** 
            * @name: busca_lugar_menos_utilizado_por 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMenosUtilizadoPor($lugares,$idgrupo,$los_demas,$asobjects = false){ 
               $sql = "select  public.busca_lugar_menos_utilizado_por($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($lugares, "text"), PartModel::sqlValue($idgrupo, "text"), PartModel::sqlValue($los_demas, "bool")), $asobjects);
           } 
          /** 
            * @name: dame_total_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTotalOtrosRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_total_otros_recursos_asignados($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_zpriori 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameZpriori($clasif,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_zpriori($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($clasif, "text"), PartModel::sqlValue($idperiodo, "text")), $asobjects);
           } 
          /** 
            * @name: dame_grupos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGrupos($idesp,$idnivel,$idclasif,$asobjects = false){ 
               $sql = "select  public.dame_grupos($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idclasif, "text")), $asobjects);
           } 
          /** 
            * @name: dame_grupos_de_brigadas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGruposDeBrigadas($brigadas,$idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_grupos_de_brigadas($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brigadas, "text"), PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: valor_restriccion_generica 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionGenerica($ptipo,$id,$idperiodo,$pdia,$pturno,$tipo_restricc,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_generica($1,$2,$3,$4,$5,$6)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($ptipo, "text"), PartModel::sqlValue($id, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($tipo_restricc, "text")), $asobjects);
           } 
          /** 
            * @name: dame_lugares_usados_alrededor_de 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresUsadosAlrededorDe($brigadas,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_lugares_usados_alrededor_de($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brigadas, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number")), $asobjects);
           } 
          /** 
            * @name: quita_segun_capacidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function QuitaSegunCapacidad($brigadas,$lugares,$asobjects = false){ 
               $sql = "select  public.quita_segun_capacidad($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($brigadas, "text"), PartModel::sqlValue($lugares, "text")), $asobjects);
           } 
          /** 
            * @name: dia_minimo_impacto 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DiaMinimoImpacto($idclasif,$idesp,$idnivel,$idperiodo,$dia_ini,$dia_fin,$asobjects = false){ 
               $sql = "select  public.dia_minimo_impacto($1,$2,$3,$4,$5,$6)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idclasif, "text"), PartModel::sqlValue($idesp, "text"), PartModel::sqlValue($idnivel, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($dia_ini, "number"), PartModel::sqlValue($dia_fin, "number")), $asobjects);
           } 
          /** 
            * @name: dame_lista_id_profes_asignados_al_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdProfesAsignadosAlGrupo($idplantilla,$idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_profes_asignados_al_grupo($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: get_max_colocation 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMaxColocation($arr,$duracion,$asobjects = false){ 
               $sql = "select  public.get_max_colocation($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($arr, "text"), PartModel::sqlValue($duracion, "number")), $asobjects);
           } 
          /** 
            * @name: posible_inicio 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PosibleInicio($idplantilla,$dia,$turno,$idbrigada,$plugares,$pprofesores,$asobjects = false){ 
               $sql = "select  public.posible_inicio($1,$2,$3,$4,$5,$6)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text"), PartModel::sqlValue($dia, "number"), PartModel::sqlValue($turno, "number"), PartModel::sqlValue($idbrigada, "text"), PartModel::sqlValue($plugares, "text"), PartModel::sqlValue($pprofesores, "text")), $asobjects);
           } 
          /** 
            * @name: first 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function First_v2($param1,$asobjects = false){ 
               $sql = "select  public.first($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($param1, "text")), $asobjects);
           } 
          /** 
            * @name: dame_plantilla_de_grupo_de_recursos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlantillaDeGrupoDeRecursos($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_plantilla_de_grupo_de_recursos($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgrupo, "text")), $asobjects);
           } 
          /** 
            * @name: esta_otro_recurso_asignado 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EstaOtroRecursoAsignado($idrecurso,$idplantilla,$asobjects = false){ 
               $sql = "select  public.esta_otro_recurso_asignado($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idrecurso, "text"), PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_otros_recursos_sin_asignar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameOtrosRecursosSinAsignar($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_otros_recursos_sin_asignar($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idplantilla, "text")), $asobjects);
           } 
          /** 
            * @name: dame_max_priori_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioriOtroRecurso($idgr,$asobjects = false){ 
               $sql = "select  public.dame_max_priori_otro_recurso($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idgr, "text")), $asobjects);
           } 
          /** 
            * @name: sube_prioridad_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function SubePrioridadClasif($idclasif,$asobjects = false){ 
               $sql = "select  public.sube_prioridad_clasif($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idclasif, "text")), $asobjects);
           } 
          /** 
            * @name: baja_prioridad_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BajaPrioridadClasif($idclasif,$asobjects = false){ 
               $sql = "select  public.baja_prioridad_clasif($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idclasif, "text")), $asobjects);
           } 
          /** 
            * @name: arregla_prioridades_clasif_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ArreglaPrioridadesClasifAfterDelete($asobjects = false){ 
               $sql = "select  public.arregla_prioridades_clasif_after_delete()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: arregla_prioridades_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ArreglaPrioridadesClasif($pcreador,$asobjects = false){ 
               $sql = "select  public.arregla_prioridades_clasif($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($pcreador, "text")), $asobjects);
           } 
          /** 
            * @name: pldbg_abort_target 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgAbortTarget($session,$asobjects = false){ 
               $sql = "select  public.pldbg_abort_target($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_attach_to_port 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgAttachToPort($portnumber,$asobjects = false){ 
               $sql = "select  public.pldbg_attach_to_port($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($portnumber, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_continue 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgContinue($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_continue($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_create_listener 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgCreateListener($asobjects = false){ 
               $sql = "select  public.pldbg_create_listener()"; 
               return $this->getAnythingParams($sql, array(), $asobjects);
           } 
          /** 
            * @name: pldbg_deposit_value 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgDepositValue($session,$varname,$linenumber,$value,$asobjects = false){ 
               $sql = "select  public.pldbg_deposit_value($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($varname, "text"), PartModel::sqlValue($linenumber, "number"), PartModel::sqlValue($value, "text")), $asobjects);
           } 
          /** 
            * @name: pldbg_drop_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgDropBreakpoint($session,$func,$linenumber,$asobjects = false){ 
               $sql = "select  public.pldbg_drop_breakpoint($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($func, "text"), PartModel::sqlValue($linenumber, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_breakpoints 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetBreakpoints($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_breakpoints($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_source 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetSource($session,$func,$asobjects = false){ 
               $sql = "select  public.pldbg_get_source($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($func, "text")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_stack 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetStack($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_stack($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_get_variables 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetVariables($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_variables($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_select_frame 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSelectFrame($session,$frame,$asobjects = false){ 
               $sql = "select * from public.pldbg_select_frame($1,$2)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($frame, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_set_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSetBreakpoint($session,$func,$linenumber,$asobjects = false){ 
               $sql = "select  public.pldbg_set_breakpoint($1,$2,$3)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($func, "text"), PartModel::sqlValue($linenumber, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_set_global_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSetGlobalBreakpoint($session,$func,$linenumber,$targetpid,$asobjects = false){ 
               $sql = "select  public.pldbg_set_global_breakpoint($1,$2,$3,$4)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number"), PartModel::sqlValue($func, "text"), PartModel::sqlValue($linenumber, "number"), PartModel::sqlValue($targetpid, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_step_into 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgStepInto($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_step_into($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_step_over 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgStepOver($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_step_over($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: pldbg_wait_for_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgWaitForBreakpoint($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_wait_for_breakpoint($1)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($session, "number")), $asobjects);
           } 
          /** 
            * @name: actualiza_restriccion_real 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaRestriccionReal($idobjeto,$idactividad,$idnominal,$idperiodo,$pdia,$pturno,$por_actividad,$tabla_real,$asobjects = false){ 
               $sql = "select  public.actualiza_restriccion_real($1,$2,$3,$4,$5,$6,$7,$8)"; 
               return $this->getAnythingParams($sql, array(PartModel::sqlValue($idobjeto, "text"), PartModel::sqlValue($idactividad, "text"), PartModel::sqlValue($idnominal, "text"), PartModel::sqlValue($idperiodo, "text"), PartModel::sqlValue($pdia, "number"), PartModel::sqlValue($pturno, "number"), PartModel::sqlValue($por_actividad, "bool"), PartModel::sqlValue($tabla_real, "text")), $asobjects);
           } 
     } 
