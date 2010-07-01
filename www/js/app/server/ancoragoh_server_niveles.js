ancoragoh.server.niveles = {
    add: function(descripcion, fcomplete){
        ajax_execute_event({
                   app_event_add_Nivel: "yes", 
                   field_Nivel_descripcion: descripcion
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_Nivel: "yes", 
                   field_Nivel_id: id
                  }, fcomplete);
    },
    update: function(id, descripcion, fcomplete){
        ajax_execute_event({
                   app_event_set_Nivel: "yes", 
                   field_Nivel_id: id,
                   field_Nivel_descripcion: descripcion                          
                  }, fcomplete);
    }
};