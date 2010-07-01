ancoragoh.server.especialidades = {
    add: function(descripcion, fcomplete){
        ajax_execute_event({
                        app_event_add_Especialidad: "yes", 
                        field_Especialidad_descripcion: descripcion
                      }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                       app_event_del_Especialidad: "yes", 
                       field_Especialidad_id: id
                        }, fcomplete);
    },
    update: function(id, descripcion, fcomplete){
        ajax_execute_event({
                   app_event_set_Especialidad: "yes", 
                   field_Especialidad_id: id,
                   field_Especialidad_descripcion: descripcion                          
                  }, fcomplete);
    }
};