ancoragoh.server.brigadas = {
    add: function(descripcion, matricula, especialidad, nivel, fcomplete){
        ajax_execute_event({
                   app_event_add_Brigada: "yes", 
                   field_Brigada_descripcion: descripcion,
                   field_Brigada_matricula: matricula,
                   field_Brigada_especialidad: especialidad,
                   field_Brigada_nivel: nivel
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_Brigada: "yes", 
                   field_Brigada_id: id
                  }, fcomplete);
    },
    update: function(id, descripcion, matricula, especialidad, nivel, fcomplete){
        ajax_execute_event({
                   app_event_set_Brigada: "yes", 
                   field_Brigada_id: id,
                   field_Brigada_descripcion: descripcion,
                   field_Brigada_matricula: matricula,
                   field_Brigada_especialidad: especialidad,
                   field_Brigada_nivel: nivel
                  }, fcomplete);
    },
    setRestriccion: function (idbrigada, idperiodo, dia, turno, idperiodo){
    }
};