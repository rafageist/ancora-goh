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
               $sql = "select  public.dame_asig_segun_esp_anno_per('$idesp','$idnivel','$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_asignatura_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsignaturaDesglose($param1,$asobjects = false){ 
               $sql = "select  public.dame_asignatura_desglose('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_asignatura_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsignaturaPlantillaDeActividad($id,$asobjects = false){ 
               $sql = "select  public.dame_asignatura_plantilla_de_actividad('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_brigadas_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameBrigadasDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select * from public.dame_brigadas_de_grupo('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_brigadas_sin_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameBrigadasSinGrupo($idesp,$idnivel,$idclasif,$idplanificacion,$asobjects = false){ 
               $sql = "select  public.dame_brigadas_sin_grupo('$idesp','$idnivel','$idclasif','$idplanificacion')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: busca_lugar_menos_costoso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMenosCostoso($origenes,$destinos,$asobjects = false){ 
               $sql = "select  public.busca_lugar_menos_costoso('$origenes','$destinos')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_act_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantActDesglose($desglose,$asobjects = false){ 
               $sql = "select  public.dame_cant_act_desglose('$desglose')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_asig_de_esp_anno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAsigDeEspAnno($idesp,$idnivel,$asobjects = false){ 
               $sql = "select  public.dame_cant_asig_de_esp_anno('$idesp','$idnivel')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantClasif($asobjects = false){ 
               $sql = "select  public.dame_cant_clasif()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_dias 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantDias($idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_dias('$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_grupos_de_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantGruposDeRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_grupos_de_recursos_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_lugares_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantLugaresAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_lugares_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantOtrosRecursosAsignados($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_cant_otros_recursos_asignados('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_profes_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantProfesAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_profes_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_rest_profe 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantRestProfe($idprofe,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_rest_profe('$idprofe','$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_turnos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantTurnos($idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_turnos('$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_capacidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCapacidad($lug,$asobjects = false){ 
               $sql = "select  public.dame_capacidad('$lug')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_clasif_de_asignaturas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasifDeAsignaturas($asignatura,$periodo,$asobjects = false){ 
               $sql = "select  public.dame_clasif_de_asignaturas('$asignatura','$periodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_clasif_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasifPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_clasif_plantilla_de_actividad('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_clasificacion_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameClasificacionDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_clasificacion_de_grupo('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_costo_trasladar_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCostoTrasladarRecurso($idrecurso,$idlugar,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_costo_trasladar_recurso('$idrecurso','$idlugar','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_creador_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_asignatura('$idasig')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_creador_de_clasifact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeClasifact($idclasif,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_clasifact('$idclasif')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_creador_de_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDeEspecialidad($esp,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_especialidad('$esp')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_creador_de_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDePlanificacion($id,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_planificacion('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_creador_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCreadorDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_creador_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_otros_recursos_imprescindibles 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantOtrosRecursosImprescindibles($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_cant_otros_recursos_imprescindibles('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_desglose_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDesgloseDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_desglose_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_dia_real_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDiaRealPlanificacion($pid,$dia,$asobjects = false){ 
               $sql = "select  public.dame_dia_real_planificacion('$pid',$dia)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_duracion_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionActividad($id,$asobjects = false){ 
               $sql = "select  public.dame_duracion_actividad('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_duracion_clasif_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionClasifDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_duracion_clasif_de_actividad('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_duracion_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_duracion_plantilla_de_actividad('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_duracion_turno_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameDuracionTurnoPeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_duracion_turno_periodo('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_especialidad_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_asignatura('$idasig')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_especialidad_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeBrigada($idbrigada,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_brigada('$idbrigada')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_especialidad_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameEspecialidadDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_especialidad_de_grupo('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_gasto_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGastoDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_gasto_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_gasto_lugares_en_el_dia 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGastoLugaresEnElDia($lugares,$periodo,$pdia,$asobjects = false){ 
               $sql = "select  public.dame_gasto_lugares_en_el_dia('$lugares','$periodo',$pdia)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGrupo($brg,$clasif,$asobjects = false){ 
               $sql = "select  public.dame_grupo('$brg','$clasif')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_grupos_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGruposDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_grupos_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_hora_inicial_turno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameHoraInicialTurno($idperiodo,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_hora_inicial_turno('$idperiodo',$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_id_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdPlantilla($idasig,$idperiodo,$orden,$asobjects = false){ 
               $sql = "select  public.dame_id_plantilla('$idasig','$idperiodo',$orden)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDeBrigada($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_brigada('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDeEspecialidad($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_especialidad('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_idobjeto_de_restriccion_de_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameIdobjetoDeRestriccionDePeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_idobjeto_de_restriccion_de_periodo('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_brigadas_de_esp 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdBrigadasDeEsp($esp,$niv,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_brigadas_de_esp('$esp','$niv')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_brigadas_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdBrigadasDeGrupo($pgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_brigadas_de_grupo('$pgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdClasif($asobjects = false){ 
               $sql = "select  public.dame_lista_id_clasif()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_grupos_de_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdGruposDeRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_grupos_de_recursos_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_lugares_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdLugaresAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_lugares_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdOtrosRecursosAsignados($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_otros_recursos_asignados('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_profes_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdProfesAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_profes_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lugares_no_asignados_a 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresNoAsignadosA($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_lugares_no_asignados_a('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lugares_utilizados_en_el_dia 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresUtilizadosEnElDia($pgrupo,$periodo,$pdia,$asobjects = false){ 
               $sql = "select  public.dame_lugares_utilizados_en_el_dia('$pgrupo','$periodo',$pdia)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_matricula 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMatricula($brg,$asobjects = false){ 
               $sql = "select  public.dame_matricula('$brg')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_matricula_total 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMatriculaTotal($brigadas,$asobjects = false){ 
               $sql = "select  public.dame_matricula_total('$brigadas')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_orden_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxOrdenAct($iddesglose,$asobjects = false){ 
               $sql = "select  public.dame_max_orden_act('$iddesglose')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: if 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function goIf($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.if($param1,$param2,$param3)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_priori_clasif_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioriClasifAct($pcreador,$asobjects = false){ 
               $sql = "select  public.dame_max_priori_clasif_act('$pcreador')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_prioridad_de_pxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadDePxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_de_pxact('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_prioridad_lxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadLxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_lxact('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_prioridad_rxact 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioridadRxact($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_max_prioridad_rxact('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_nivel_de_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeAsignatura($idasig,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_asignatura('$idasig')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_nivel_de_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeBrigada($idbrigada,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_brigada('$idbrigada')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_nivel_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameNivelDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_nivel_de_grupo('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_periodo_desglose 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePeriodoDesglose($param1,$asobjects = false){ 
               $sql = "select  public.dame_periodo_desglose('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_periodo_plantilla_de_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePeriodoPlantillaDeActividad($param1,$asobjects = false){ 
               $sql = "select  public.dame_periodo_plantilla_de_actividad('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_planificacion_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlanificacionDeGrupo($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_planificacion_de_grupo('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_planificacion_de_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlanificacionDePeriodo($pid,$asobjects = false){ 
               $sql = "select  public.dame_planificacion_de_periodo('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_profesores_no_asignados_a 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameProfesoresNoAsignadosA($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_profesores_no_asignados_a('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_tipo_consec_rest_cond 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoConsecRestCond($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_consec_rest_cond('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_tipo_hijo_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoHijoHrt($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_hijo_hrt('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_tipo_padre_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTipoPadreHrt($pid,$asobjects = false){ 
               $sql = "select  public.dame_tipo_padre_hrt('$pid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_uso_de_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameUsoDeOtroRecurso($id,$asobjects = false){ 
               $sql = "select  public.dame_uso_de_otro_recurso('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dias_se_solapan 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DiasSeSolapan($idperiodo1,$dia1,$idperiodo2,$dia2,$asobjects = false){ 
               $sql = "select  public.dias_se_solapan('$idperiodo1',$dia1,'$idperiodo2',$dia2)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: es_lugar_prohibido 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsLugarProhibido($pgrupo,$plugar,$asobjects = false){ 
               $sql = "select  public.es_lugar_prohibido('$pgrupo','$plugar')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: esta_dia_en_rango_planificacion 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EstaDiaEnRangoPlanificacion($pdia,$idperiodo,$dia_ini,$dia_fin,$asobjects = false){ 
               $sql = "select  public.esta_dia_en_rango_planificacion($pdia,'$idperiodo',$dia_ini,$dia_fin)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: existe_grupo_de_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExisteGrupoDeProfesor($profe_para_act,$pgrupo,$asobjects = false){ 
               $sql = "select  public.existe_grupo_de_profesor('$profe_para_act','$pgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: first 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function First($param1,$param2,$asobjects = false){ 
               $sql = "select  public.first('$param1','$param2')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: greatest 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Greatest($param1,$param2,$asobjects = false){ 
               $sql = "select  public.greatest('$param1','$param2')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: greatest 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Greatest_v2($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.greatest('$param1','$param2','$param3')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: if 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function goIf_v2($param1,$param2,$param3,$asobjects = false){ 
               $sql = "select  public.if($param1,'$param2','$param3')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: es_higienico_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsHigienicoProfesor($idprofesor,$idplantilla,$pdia,$asobjects = false){ 
               $sql = "select  public.es_higienico_profesor('$idprofesor','$idplantilla',$pdia)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: extraer_lugares_que_no_permanecen 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ExtraerLugaresQueNoPermanecen($lugares,$periodo,$dia,$turno,$duracion,$asobjects = false){ 
               $sql = "select  public.extraer_lugares_que_no_permanecen('$lugares','$periodo',$dia,$turno,$duracion)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: es_higienico_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EsHigienicoBrigada($idbrigada,$idplantilla,$pdia,$asobjects = false){ 
               $sql = "select  public.es_higienico_brigada('$idbrigada','$idplantilla',$pdia)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: iif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Iif($expresion,$dato1,$dato2,$asobjects = false){ 
               $sql = "select  public.iif($expresion,'$dato1','$dato2')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: max_cant_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function MaxCantAct($especialidad,$nivel,$periodo,$asobjects = false){ 
               $sql = "select  public.max_cant_act('$especialidad','$nivel','$periodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: mueve_o_crea_brigada_de_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function MueveOCreaBrigadaDeGrupo($idbrg,$idgrupo,$asobjects = false){ 
               $sql = "select  public.mueve_o_crea_brigada_de_grupo('$idbrg','$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nivela_prioridad_lxact_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadLxactAfterDelete($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_lxact_after_delete()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nivela_prioridad_lxact_after_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadLxactAfterUpdate($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_lxact_after_update()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nivela_prioridad_rxact_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NivelaPrioridadRxactAfterDelete($asobjects = false){ 
               $sql = "select  public.nivela_prioridad_rxact_after_delete()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nuevo_desglose_cuando_nueva_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NuevoDesgloseCuandoNuevaAsignatura($asobjects = false){ 
               $sql = "select  public.nuevo_desglose_cuando_nueva_asignatura()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: nuevo_desglose_cuando_nuevo_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function NuevoDesgloseCuandoNuevoPeriodo($asobjects = false){ 
               $sql = "select  public.nuevo_desglose_cuando_nuevo_periodo()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: plantilla_exige_lugar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlantillaExigeLugar($id,$asobjects = false){ 
               $sql = "select  public.plantilla_exige_lugar('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: plantilla_exige_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlantillaExigeProfesor($id,$asobjects = false){ 
               $sql = "select  public.plantilla_exige_profesor('$id')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: ocurre_causa_rest_cond 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function OcurreCausaRestCond($rc,$act,$idperiodo,$pdia,$pturno,$analiza_completo,$asobjects = false){ 
               $sql = "select  public.ocurre_causa_rest_cond('$rc','$act','$idperiodo',$pdia,$pturno,$analiza_completo)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: lugar_permanece_en 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function LugarPermaneceEn($plugar,$pperiodo,$pdia,$pturno,$duracion,$asobjects = false){ 
               $sql = "select  public.lugar_permanece_en('$plugar','$pperiodo',$pdia,$pturno,$duracion)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: rand 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Rand($asobjects = false){ 
               $sql = "select  public.rand()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: restringe_por_uso_de_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function RestringePorUsoDeOtroRecurso($idactividad,$idrecurso,$asobjects = false){ 
               $sql = "select  public.restringe_por_uso_de_otro_recurso('$idactividad','$idrecurso')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: sube_orden_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function SubeOrdenDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.sube_orden_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: turnos_se_solapan 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function TurnosSeSolapan($idperiodo1,$dia1,$turno1,$duracion1,$idperiodo2,$dia2,$turno2,$duracion2,$asobjects = false){ 
               $sql = "select  public.turnos_se_solapan('$idperiodo1',$dia1,$turno1,$duracion1,'$idperiodo2',$dia2,$turno2,$duracion2)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: quita_segun_prohibidos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function QuitaSegunProhibidos($grupos,$lugares,$asobjects = false){ 
               $sql = "select  public.quita_segun_prohibidos('$grupos','$lugares')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: restringe_segun_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function RestringeSegunActividad($idactividad,$idplantilla,$idprofesor,$idlugar,$pdia,$pturno,$idgrupo,$asobjects = false){ 
               $sql = "select  public.restringe_segun_actividad('$idactividad','$idplantilla','$idprofesor','$idlugar',$pdia,$pturno,'$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: rango 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Rango($idplantilla,$idgrupo,$minimo,$maximo,$asobjects = false){ 
               $sql = "select * from public.rango('$idplantilla','$idgrupo',$minimo,$maximo)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_rest_entre_config_distintas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestEntreConfigDistintas($rests,$per1,$per2,$dia,$turno,$asobjects = false){ 
               $sql = "select  public.valor_rest_entre_config_distintas('$rests','$per1','$per2',$dia,$turno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionAsignatura($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_asignatura('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_brigada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionBrigada($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_brigada('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_clasif_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionClasifAct($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_clasif_act('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_condicional 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionCondicional($tipo_consec,$id_consec,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_condicional('$tipo_consec','$id_consec','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_especialidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionEspecialidad($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_especialidad('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_lugar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionLugar($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_lugar('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_periodo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionPeriodo($idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_periodo('$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_profesor 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionProfesor($id,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_profesor('$id','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_zpriori 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorZpriori($clasif,$periodo,$dia,$turno,$asobjects = false){ 
               $sql = "select  public.valor_zpriori('$clasif','$periodo',$dia,$turno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: plpgsql_oid_debug 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PlpgsqlOidDebug($functionoid,$asobjects = false){ 
               $sql = "select  public.plpgsql_oid_debug('$functionoid')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_proxy_info 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetProxyInfo($asobjects = false){ 
               $sql = "select * from public.pldbg_get_proxy_info()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_wait_for_target 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgWaitForTarget($session,$asobjects = false){ 
               $sql = "select  public.pldbg_wait_for_target($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_act_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterDelete($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_delete()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_act_after_insert 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterInsert($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_insert()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_act_after_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActAfterUpdate($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_after_update()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_act_before_insert 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActBeforeInsert($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_before_insert()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_act_before_update 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActBeforeUpdate($asobjects = false){ 
               $sql = "select  public.actualiza_orden_act_before_update()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_actividades 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenActividades($asobjects = false){ 
               $sql = "select  public.actualiza_orden_actividades()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_orden_plantillas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaOrdenPlantillas($iddesglose,$asobjects = false){ 
               $sql = "select  public.actualiza_orden_plantillas('$iddesglose')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_profesor_para_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaProfesorParaGrupo($asobjects = false){ 
               $sql = "select  public.actualiza_profesor_para_grupo()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: baja_orden_de_plantilla 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BajaOrdenDePlantilla($idplantilla,$asobjects = false){ 
               $sql = "select  public.baja_orden_de_plantilla('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: bool_to_int 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BoolToInt($valor,$asobjects = false){ 
               $sql = "select  public.bool_to_int($valor)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: bool_to_str 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BoolToStr($valor,$asobjects = false){ 
               $sql = "select  public.bool_to_str($valor)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_accesos_prohibidos_al_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAccesosProhibidosAlGrupo($param1,$asobjects = false){ 
               $sql = "select  public.dame_cant_accesos_prohibidos_al_grupo('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_act 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantAct($asig,$periodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_act('$asig','$periodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: concat 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Concat($param1,$param2,$asobjects = false){ 
               $sql = "select  public.concat('$param1','$param2')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: and_mpi 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function AndMpi($listaidbrg,$cd,$ct,$idplantilla,$zona,$excepto,$asobjects = false){ 
               $sql = "select  public.and_mpi('$listaidbrg',$cd,$ct,'$idplantilla',$zona,$excepto)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: busca_lugar_mas_utilizado_por 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMasUtilizadoPor($lugares,$idgrupo,$los_demas,$asobjects = false){ 
               $sql = "select  public.busca_lugar_mas_utilizado_por('$lugares','$idgrupo',$los_demas)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: agrega_actividad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function AgregaActividad($idplantilla,$idprofesor,$idlugar,$pdia,$pturno,$idgrupo,$idrecursos,$momento,$pcreador,$asobjects = false){ 
               $sql = "select  public.agrega_actividad('$idplantilla','$idprofesor','$idlugar',$pdia,$pturno,'$idgrupo','$idrecursos',$momento,'$pcreador')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: check_hrt 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CheckHrt($hijo_brigada,$hijo_profesor,$hijo_lugar,$hijo_otro_recurso,$padre_brigada,$padre_profesor,$padre_lugar,$padre_otro_recurso,$asobjects = false){ 
               $sql = "select  public.check_hrt('$hijo_brigada','$hijo_profesor','$hijo_lugar','$hijo_otro_recurso','$padre_brigada','$padre_profesor','$padre_lugar','$padre_otro_recurso')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: crear_cadena_aleatoria 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CrearCadenaAleatoria($caracteres,$longitud,$asobjects = false){ 
               $sql = "select  public.crear_cadena_aleatoria('$caracteres',$longitud)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: crear_id_de_replica 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function CrearIdDeReplica($prefijo,$tabla,$campoid,$sufijo,$asobjects = false){ 
               $sql = "select  public.crear_id_de_replica('$prefijo','$tabla','$campoid','$sufijo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_asig_segun_esp_anno 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameAsigSegunEspAnno($idesp,$idnivel,$asobjects = false){ 
               $sql = "select  public.dame_asig_segun_esp_anno('$idesp','$idnivel')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_heredada 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionHeredada($tipo_hijo,$id_hijo,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_heredada('$tipo_hijo','$id_hijo','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: generar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function Generar($idesp,$idnivel,$idperiodo,$forma,$parche,$compactar,$parche1,$buscalugar,$asobjects = false){ 
               $sql = "select  public.generar('$idesp','$idnivel','$idperiodo',$forma,$parche,$compactar,$parche1,$buscalugar)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_cant_rest_asignatura 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameCantRestAsignatura($idasig,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_cant_rest_asignatura('$idasig','$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_target_info 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetTargetInfo($signature,$targettype,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_target_info('$signature','$targettype')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: busca_lugar_menos_utilizado_por 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BuscaLugarMenosUtilizadoPor($lugares,$idgrupo,$los_demas,$asobjects = false){ 
               $sql = "select  public.busca_lugar_menos_utilizado_por('$lugares','$idgrupo',$los_demas)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_total_otros_recursos_asignados 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameTotalOtrosRecursosAsignados($idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_total_otros_recursos_asignados('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_zpriori 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameZpriori($clasif,$idperiodo,$asobjects = false){ 
               $sql = "select  public.dame_zpriori('$clasif','$idperiodo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_grupos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGrupos($idesp,$idnivel,$idclasif,$asobjects = false){ 
               $sql = "select  public.dame_grupos('$idesp','$idnivel','$idclasif')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_grupos_de_brigadas 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameGruposDeBrigadas($brigadas,$idplantilla,$asobjects = false){ 
               $sql = "select  public.dame_grupos_de_brigadas('$brigadas','$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: valor_restriccion_generica 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ValorRestriccionGenerica($ptipo,$id,$idperiodo,$pdia,$pturno,$tipo_restricc,$asobjects = false){ 
               $sql = "select  public.valor_restriccion_generica('$ptipo','$id','$idperiodo',$pdia,$pturno,'$tipo_restricc')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lugares_usados_alrededor_de 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameLugaresUsadosAlrededorDe($brigadas,$idperiodo,$pdia,$pturno,$asobjects = false){ 
               $sql = "select  public.dame_lugares_usados_alrededor_de('$brigadas','$idperiodo',$pdia,$pturno)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: quita_segun_capacidad 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function QuitaSegunCapacidad($brigadas,$lugares,$asobjects = false){ 
               $sql = "select  public.quita_segun_capacidad('$brigadas','$lugares')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dia_minimo_impacto 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DiaMinimoImpacto($idclasif,$idesp,$idnivel,$idperiodo,$dia_ini,$dia_fin,$asobjects = false){ 
               $sql = "select  public.dia_minimo_impacto('$idclasif','$idesp','$idnivel','$idperiodo',$dia_ini,$dia_fin)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_lista_id_profes_asignados_al_grupo 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameListaIdProfesAsignadosAlGrupo($idplantilla,$idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_lista_id_profes_asignados_al_grupo('$idplantilla','$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: get_max_colocation 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function GetMaxColocation($arr,$duracion,$asobjects = false){ 
               $sql = "select  public.get_max_colocation('$arr',$duracion)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: posible_inicio 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PosibleInicio($idplantilla,$dia,$turno,$idbrigada,$plugares,$pprofesores,$asobjects = false){ 
               $sql = "select  public.posible_inicio('$idplantilla',$dia,$turno,'$idbrigada','$plugares','$pprofesores')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: first 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function First_v2($param1,$asobjects = false){ 
               $sql = "select  public.first('$param1')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_plantilla_de_grupo_de_recursos 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DamePlantillaDeGrupoDeRecursos($idgrupo,$asobjects = false){ 
               $sql = "select  public.dame_plantilla_de_grupo_de_recursos('$idgrupo')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: esta_otro_recurso_asignado 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function EstaOtroRecursoAsignado($idrecurso,$idplantilla,$asobjects = false){ 
               $sql = "select  public.esta_otro_recurso_asignado('$idrecurso','$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_otros_recursos_sin_asignar 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameOtrosRecursosSinAsignar($idplantilla,$asobjects = false){ 
               $sql = "select * from public.dame_otros_recursos_sin_asignar('$idplantilla')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: dame_max_priori_otro_recurso 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function DameMaxPrioriOtroRecurso($idgr,$asobjects = false){ 
               $sql = "select  public.dame_max_priori_otro_recurso('$idgr')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: sube_prioridad_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function SubePrioridadClasif($idclasif,$asobjects = false){ 
               $sql = "select  public.sube_prioridad_clasif('$idclasif')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: baja_prioridad_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function BajaPrioridadClasif($idclasif,$asobjects = false){ 
               $sql = "select  public.baja_prioridad_clasif('$idclasif')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: arregla_prioridades_clasif_after_delete 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ArreglaPrioridadesClasifAfterDelete($asobjects = false){ 
               $sql = "select  public.arregla_prioridades_clasif_after_delete()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: arregla_prioridades_clasif 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ArreglaPrioridadesClasif($pcreador,$asobjects = false){ 
               $sql = "select  public.arregla_prioridades_clasif('$pcreador')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_abort_target 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgAbortTarget($session,$asobjects = false){ 
               $sql = "select  public.pldbg_abort_target($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_attach_to_port 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgAttachToPort($portnumber,$asobjects = false){ 
               $sql = "select  public.pldbg_attach_to_port($portnumber)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_continue 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgContinue($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_continue($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_create_listener 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgCreateListener($asobjects = false){ 
               $sql = "select  public.pldbg_create_listener()"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_deposit_value 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgDepositValue($session,$varname,$linenumber,$value,$asobjects = false){ 
               $sql = "select  public.pldbg_deposit_value($session,'$varname',$linenumber,'$value')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_drop_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgDropBreakpoint($session,$func,$linenumber,$asobjects = false){ 
               $sql = "select  public.pldbg_drop_breakpoint($session,'$func',$linenumber)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_breakpoints 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetBreakpoints($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_breakpoints($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_source 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetSource($session,$func,$asobjects = false){ 
               $sql = "select  public.pldbg_get_source($session,'$func')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_stack 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetStack($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_stack($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_get_variables 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgGetVariables($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_get_variables($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_select_frame 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSelectFrame($session,$frame,$asobjects = false){ 
               $sql = "select * from public.pldbg_select_frame($session,$frame)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_set_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSetBreakpoint($session,$func,$linenumber,$asobjects = false){ 
               $sql = "select  public.pldbg_set_breakpoint($session,'$func',$linenumber)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_set_global_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgSetGlobalBreakpoint($session,$func,$linenumber,$targetpid,$asobjects = false){ 
               $sql = "select  public.pldbg_set_global_breakpoint($session,'$func',$linenumber,$targetpid)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_step_into 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgStepInto($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_step_into($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_step_over 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgStepOver($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_step_over($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: pldbg_wait_for_breakpoint 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function PldbgWaitForBreakpoint($session,$asobjects = false){ 
               $sql = "select * from public.pldbg_wait_for_breakpoint($session)"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
          /** 
            * @name: actualiza_restriccion_real 
            * @desc: Function that ... 
            * @updated: 20/06/10 10:40:56 
            * @version: 1.0 
            */ 
          public function ActualizaRestriccionReal($idobjeto,$idactividad,$idnominal,$idperiodo,$pdia,$pturno,$por_actividad,$tabla_real,$asobjects = false){ 
               $sql = "select  public.actualiza_restriccion_real('$idobjeto','$idactividad','$idnominal','$idperiodo',$pdia,$pturno,$por_actividad,'$tabla_real')"; 
               if ($asobjects == true) return $this->db->getAsObjects($sql);
               return $this->db->getAsArray($sql);
           } 
     } 
