ancoragoh.server.otrosrecursos = {
    add: function(nombre, fcomplete){
        ajax_execute_event({
                   app_event_add_OtroRecurso: "yes", 
                   field_OtroRecurso_nombre: nombre
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_OtroRecurso: "yes", 
                   field_OtroRecurso_id: id
                  }, fcomplete);
    },
    update: function(id, nombre, fcomplete){
        ajax_execute_event({
                   app_event_set_OtroRecurso: "yes", 
                   field_OtroRecurso_nombre: nombre,
                   field_OtroRecurso_id: id                           
                  }, fcomplete);
    }
};