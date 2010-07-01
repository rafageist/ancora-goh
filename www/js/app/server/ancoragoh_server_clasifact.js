ancoragoh.server.clasifact = {
    add: function(descripcion, duracion, gasto, prioridad, fcomplete){
        ajax_execute_event({
                   app_event_add_ClasificacionDeActividad: "yes", 
                   field_ClasificacionDeActividad_descripcion: descripcion,
                   field_ClasificacionDeActividad_duracion: duracion,
                   field_ClasificacionDeActividad_gasto: gasto,
                   field_ClasificacionDeActividad_prioridad: prioridad   
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_ClasificacionDeActividad: "yes", 
                   field_ClasificacionDeActividad_id: id
                  }, fcomplete);
    },
    update: function(id, descripcion, duracion, gasto, prioridad, fcomplete){
        ajax_execute_event({
                   app_event_set_ClasificacionDeActividad: "yes", 
                   field_ClasificacionDeActividad_descripcion: descripcion,
                   field_ClasificacionDeActividad_duracion: duracion,
                   field_ClasificacionDeActividad_gasto: gasto,
                   field_ClasificacionDeActividad_id: id,
                   field_ClasificacionDeActividad_prioridad: prioridad   
                  }, fcomplete);
    },
    subePrioridad: function(pid, fcomplete){
	    ajax_execute_event({
          app_event_set_ClasifAct_subeprioridad: "yes", 
          id: pid   
        }, fcomplete);
    },
    bajaPrioridad: function(pid, fcomplete){
		ajax_execute_event({
	        app_event_set_ClasifAct_bajaprioridad: "yes", 
	        id: pid   
        }, fcomplete);
    },
    updateZpriori: function(idclasif, idperiodo, zp, fcomplete){
    	  ajax_execute_event({
              app_event_custom_set_zpriori: "yes", 
              clasif: idclasif,
              periodo: idperiodo,
              zpriori: zp   
             }, fcomplete);
    }
};