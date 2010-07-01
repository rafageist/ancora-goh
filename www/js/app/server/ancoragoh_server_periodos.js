ancoragoh.server.periodos = {
    add: function(descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion, fcomplete){
        ajax_execute_event({
               app_event_add_Periodo: "yes", 
               field_Periodo_descripcion: descripcion,
               field_Periodo_cant_dias: cant_dias,
               field_Periodo_cant_turnos: cant_turnos,
               field_Periodo_hora_inicial: cant_dias,
               field_Periodo_duracion_del_turno: duracion_del_turno,
               field_Periodo_planificacion: planificacion
              }, fcomplete);
    },
    update: function(id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion, fcomplete){
        ajax_execute_event({
               app_event_set_Periodo: "yes", 
               field_Periodo_id: id,
               field_Periodo_descripcion: descripcion,
               field_Periodo_cant_dias: cant_dias,
               field_Periodo_cant_turnos: cant_turnos,
               field_Periodo_hora_inicial: cant_dias,
               field_Periodo_duracion_del_turno: duracion_del_turno,
               field_Periodo_planificacion: planificacion
              }, fcomplete);
    },
    updatePeriodicidades: function(id, periodicidades, fcomplete){
		  // Guardando las periodicidades
	        ajax_execute_event({
	                       app_event_custom_set_Periodicidades: "yes", 
	                       field_Periodo_id: id,
	                       field_Periodicidades: periodicidades
	                      },fcomplete);
    },
    updateRestricciones: function(id, restricciones, fcomplete){
    	// Guardando las restricciones del periodo
		ajax_execute_event({
                   app_event_custom_set_Restricciones_Periodo: "yes", 
                   field_Periodo_id: id,
                   field_Restricciones: restricciones
                  }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                       app_event_del_Periodo: "yes", 
                       field_Periodo_id: id
                       }, fcomplete);
    }
};