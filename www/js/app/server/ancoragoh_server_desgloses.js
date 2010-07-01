ancoragoh.server.desgloses = {
    actualizar: function(id, asignatura, periodo, mindias, maxdias, respeta, mismodia){
         ajax_execute_event({
                    app_event_set_Desglose: "yes", 
                    field_Desglose_id: id,
                    field_Desglose_asignatura: asignatura,
                    field_Desglose_periodo: periodo,
                    field_Desglose_min_dias: mindias,
                    field_Desglose_max_dias: maxdias,
                    field_Desglose_respetar_orden: respeta,
                    field_Desglose_mismo_dia: mismodia
                   });
    }
};