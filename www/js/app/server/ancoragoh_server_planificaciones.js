ancoragoh.server.planificaciones = {
    add: function(descripcion, fechaini, fechafin, fcomplete){
        ajax_execute_event({
               app_event_add_Planificacion: "yes", 
               field_Planificacion_descripcion: descripcion,
               field_Planificacion_fecha_inicial: fechaini,
               edtFechaFinal: fechafin
              }, fcomplete);
    },
    remove: function (id, fcomplete){
       ajax_execute_event({
                app_event_del_Planificacion: "yes", 
                field_Planificacion_id: id
                }, fcomplete);
    },
    update: function(id, descripcion, fechaini, fechafin, fcomplete){
        ajax_execute_event({
               app_event_set_Planificacion: "yes", 
               field_Planificacion_id: id,
               field_Planificacion_descripcion: descripcion,
               field_Planificacion_fecha_inicial: fechaini,
               edtFechaFinal: fechafin
              },  fcomplete);
    }
};