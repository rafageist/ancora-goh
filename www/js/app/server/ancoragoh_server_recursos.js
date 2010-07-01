ancoragoh.server.recursos = {
    agregarProfesorParaActividad: function(idplantilla, idprofesor, fcomplete){
        ajax_execute_event({
                        app_event_add_ProfesorParaActividad: "yes",
                        field_ProfesorParaActividad_profesor: idprofesor,
                        field_ProfesorParaActividad_plantilla: idplantilla,
                        field_ProfesorParaActividad_prioridad: 1
                        }, fcomplete);
    },
    actualizarProfesorParaActividad: function(id, idplantilla, idprofesor, prioridad, fcomplete){
        ajax_execute_event({
                        app_event_set_ProfesorParaActividad: "yes",
                        field_ProfesorParaActividad_id: id,
                        field_ProfesorParaActividad_profesor: idprofesor,
                        field_ProfesorParaActividad_plantilla: idplantilla,
                        field_ProfesorParaActividad_prioridad: prioridad
                        }, fcomplete);
    },
	eliminarProfesorParaActividad: function(id, fcomplete){
		 ajax_execute_event({
						app_event_del_ProfesorParaActividad: "yes",
						field_ProfesorParaActividad_id: id
						}, fcomplete);
	},
    actualizarProfesorParaGrupo: function(idpxact, idgrupo, fcomplete){
        ajax_execute_event({
            app_event_custom_ActualizaProfesorParaGrupo: "yes",
            pxact: idpxact,
            grupo: idgrupo
        }, fcomplete);
    },
    eliminarProfesorParaGrupo: function(idpxact, idgrupo, fcomplete){
        ajax_execute_event({
            app_event_del_ProfesorParaGrupo: "yes",
            field_ProfesorParaGrupo_profesor_para_actividad: idpxact,
            field_ProfesorParaGrupo_grupo: idgrupo
        }, fcomplete);
    },
    eliminarGruposDeProfesor: function(idpxact){
        ajax_execute_event({
            app_event_custom_EliminarGruposDeProfesor: "yes",
            pxact: idpxact
        });
    },
    agregarLugarParaActividad: function(idplantilla, idlugar, fcomplete){
        ajax_execute_event({
                        app_event_add_LugarParaActividad: "yes",
                        field_LugarParaActividad_lugar: idlugar,
                        field_LugarParaActividad_plantilla: idplantilla,
                        field_LugarParaActividad_prioridad: 1
                        }, fcomplete);
    },
    eliminarLugarParaActividad: function(idplantilla,idlugar, fcomplete){
        ajax_execute_event({
            app_event_del_LugarParaActividad: "yes",
            field_LugarParaActividad_lugar: idlugar,
            field_LugarParaActividad_plantilla: idplantilla
            }, fcomplete);
    },
    actualizaPrioridadLxact: function(idplantilla,idlugar, prioridad, fcomplete){
        ajax_execute_event({
            app_event_set_LugarParaActividad: "yes",
            field_LugarParaActividad_lugar: idlugar,
            field_LugarParaActividad_plantilla: idplantilla,
            field_LugarParaActividad_prioridad: prioridad
            }, fcomplete);
    },
    nuevoGrupoDeRecursosParaActividad: function(idplantilla, fcomplete){
         ajax_execute_event({
            app_event_add_OtroGrupoDeRecursosParaActividad: "yes",
            field_OtroGrupoDeRecursosParaActividad_plantilla: idplantilla,
            field_OtroGrupoDeRecursosParaActividad_prioridad: 1,
            field_OtroGrupoDeRecursosParaActividad_necesarios: 1,
            field_OtroGrupoDeRecursosParaActividad_imprescindibles: 1
            }, fcomplete);
    },
    actualizaPrioridadRxact: function(idrxact, prioridad, fcomplete){
        ajax_execute_event({
            app_event_custom_set_PrioridadRxact: "yes",
            rxact: idrxact,
            prioridad: prioridad
            }, fcomplete);
    },
    eliminarGrupoDeRecursosParaActividad: function(idrxact, fcomplete){
        ajax_execute_event({
            app_event_del_OtroGrupoDeRecursosParaActividad: "yes",
            field_OtroGrupoDeRecursosParaActividad_id: idrxact
            }, fcomplete);
    },
    eliminarRecursoParaActividad: function (idgr, idrxact, fcomplete){
    	ajax_execute_event({
            app_event_del_OtroRecursoParaActividad: "yes",
            field_OtroRecursoParaActividad_grupo_de_recursos: idgr,
            field_OtroRecursoParaActividad_otro_recurso: idrxact
            }, fcomplete);
    },
    agregarOtroRecursoParaActividad: function(idrec, prioridad, idgr, fcomplete){
    	ajax_execute_event({
            app_event_add_OtroRecursoParaActividad: "yes",
            field_OtroRecursoParaActividad_grupo_de_recursos: idgr,
            field_OtroRecursoParaActividad_prioridad: prioridad,
            field_OtroRecursoParaActividad_otro_recurso: idrec
            }, fcomplete);
    },
    actualizarImprescindibles: function(idgr, imp, fcomplete){
    	ajax_execute_event({
            app_event_set_GrupoRecursos_imprescindibles: "yes",
            id: idgr,
            imp: imp
            }, fcomplete);
    },
    actualizarNecesarios: function (idgr, nec, fcomplete){
    	ajax_execute_event({
            app_event_set_GrupoRecursos_necesarios: "yes",
            id: idgr,
            nec: nec
            }, fcomplete);
    },
    actualizaPrioridadPxact: function(idpxact, priori, fcomplete){
        ajax_execute_event({
            app_event_set_prioridad_pxact: "yes",
            pxact: idpxact,
            prioridad: priori
            }, fcomplete);
    },
};
