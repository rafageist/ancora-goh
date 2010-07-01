ancoragoh.server.grupos = {
    agregar: function(descripcion, clasificacion, especialidad, nivel, planificacion, fcomplete){
         ajax_execute_event({
                    app_event_add_Grupo: "yes", 
                    field_Grupo_descripcion: descripcion,
                    field_Grupo_clasificacion: clasificacion,
                    field_Grupo_especialidad: especialidad,
                    field_Grupo_nivel: nivel,
                    field_Grupo_planificacion: planificacion
                   }, fcomplete);
    },
    modificar: function(id, descripcion, clasificacion, especialidad, nivel, planificacion, fcomplete){
         ajax_execute_event({
                    app_event_set_Grupo: "yes", 
                    field_Grupo_id: id,
                    field_Grupo_descripcion: descripcion,
                    field_Grupo_clasificacion: clasificacion,
                    field_Grupo_especialidad: especialidad,
                    field_Grupo_nivel: nivel,
                    field_Grupo_planificacion: planificacion
                   }, fcomplete);
    },
    eliminar: function(id, fcomplete){
         ajax_execute_event({
                    app_event_del_Grupo: "yes", 
                    field_Grupo_id: id
                   }, fcomplete);
    },
    moverBrigadaDeGrupo: function(idbrg, idgrupo, fcomplete){
         ajax_execute_event({
                        app_event_custom_MoverBrigadaDeGrupo: "yes", 
                        brigada: idbrg,
                        grupo: idgrupo
                       }, fcomplete);
    },
    removeBrigadaDeGrupo: function (idbrg, idesp, idnivel, idclasif, idplanif, fcomplete){
         ajax_execute_event({
                        app_event_custom_EliminaBrigadaDeGrupo: "yes", 
                        brigada: idbrg,
                        especialidad: idesp,
                        nivel: idnivel,
                        clasificacion: idclasif,
                        planificacion: idplanif
                       }, fcomplete);
    }
};