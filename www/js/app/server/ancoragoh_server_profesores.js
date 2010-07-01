ancoragoh.server.profesores = {
    add: function(nombre, fcomplete){
        ajax_execute_event({
                   app_event_add_Profesor: "yes", 
                   field_Profesor_nombre: nombre
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_Profesor: "yes", 
                   field_Profesor_id: id
                  }, fcomplete);
    },
    update: function(id, nombre, fcomplete){
        ajax_execute_event({
                   app_event_set_Profesor: "yes", 
                   field_Profesor_id: id,
                   field_Profesor_nombre: nombre                          
                  }, fcomplete);
    }
};