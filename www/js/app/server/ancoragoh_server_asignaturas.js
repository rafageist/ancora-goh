ancoragoh.server.asignaturas = {
    add: function(descripcion, especialidad, nivel, fcomplete){
        ajax_execute_event({
                   app_event_add_Asignatura: "yes", 
                   field_Asignatura_descripcion: descripcion,
                   field_Asignatura_especialidad: especialidad,
                   field_Asignatura_nivel: nivel
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_Asignatura: "yes", 
                   field_Asignatura_id: id
                  }, fcomplete);
    },
    update: function(id, descripcion, especialidad, nivel, fcomplete){
        ajax_execute_event({
                   app_event_set_Asignatura: "yes", 
                   field_Asignatura_id: id,
                   field_Asignatura_descripcion: descripcion,
                   field_Asignatura_especialidad: especialidad,
                   field_Asignatura_nivel: nivel
                  }, fcomplete);
    }
};