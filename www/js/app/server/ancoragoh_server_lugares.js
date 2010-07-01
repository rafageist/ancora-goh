ancoragoh.server.lugares = {
    add: function(nombre, capacidad, fcomplete){
       ajax_execute_event({
                   app_event_add_Lugar: "yes", 
                   field_Lugar_nombre: nombre,
                   field_Lugar_capacidad: capacidad
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_Lugar: "yes", 
                   field_Lugar_id: id
                  }, fcomplete);
    },
    update: function(id, nombre, capacidad, fcomplete){
        ajax_execute_event({
                   app_event_set_Lugar: "yes", 
                   field_Lugar_nombre: nombre,
                   field_Lugar_capacidad: capacidad,
                   field_Lugar_id: id                           
                  }, fcomplete);
    },
    updateCostosDeTraslado: function(porigen, pdestino, pcosto, fcomplete){
        ajax_execute_event({
            app_event_custom_set_costotraslado: "yes", 
            origen: porigen,
            destino: pdestino,
            costo: pcosto                           
           }, fcomplete);	
    }
};