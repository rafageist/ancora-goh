ancoragoh.client.forms.periodo = {
    event: {
		flag_planificacion: "",
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						 ancoragoh.client.browsers.planificaciones.showPeriodos(ancoragoh.client.forms.periodo.event.flag_planificacion);
						 ancoragoh.client.showMessage(ancoragoh.client.forms.periodo.event.message);
					}
		},
		agregar: function(){
			var descripcion = $("#field_Periodo_Descripcion").val();
            var cant_dias = $("#field_Periodo_Cant_dias").val();
            var cant_turnos = $("#field_Periodo_Cant_turnos").val();
            var hora_inicial = $("#field_Periodo_Hora_inicial").val();
            var duracion_del_turno = $("#field_Periodo_Duracion_del_turno").val();
            var planificacion = $("#field_Periodo_Planificacion").val();
            this.flag_planificacion = planificacion;
            this.message = 'Per&iacute;odo insertado satisfactoriamente';
            ancoragoh.server.periodos.add(descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion, this.fcomplete);
            ancoragoh.client.hidewin("new_periodo");
		},
		modificar: function(){
			var id = $("#field_Periodo_Id").val();
			var descripcion = $("#field_Periodo_Descripcion").val();
            var cant_dias = $("#field_Periodo_Cant_dias").val();
            var cant_turnos = $("#field_Periodo_Cant_turnos").val();
            var hora_inicial = $("#field_Periodo_Hora_inicial").val();
            var duracion_del_turno = $("#field_Periodo_Duracion_del_turno").val();
            var planificacion = $("#field_Periodo_Planificacion").val();
           
            this.flag_planificacion = planificacion;
            
            var id = $("#data_id_periodo").val();
            this.message = 'Per&iacute;odo actualizado satisfactoriamente';
            ancoragoh.server.periodos.update(id, descripcion, cant_dias, cant_turnos, hora_inicial, duracion_del_turno, planificacion, this.fcomplete);
            ancoragoh.client.hidewin("edit_periodo");
		},
		eliminar: function(){
			var id = $("#field_Periodo_Id").val();
			this.message = 'Per&iacute;odo eliminado satisfactoriamente';
			ancoragoh.server.periodos.remove(id, this.fcomplete);
            ancoragoh.client.hidewin("edit_periodo");
		},
		cancelar: function(){
            ancoragoh.client.hidewin("edit_periodo");
            ancoragoh.client.hidewin("new_periodo");
		}
    },
    showForNew: function(idplanif){
    	ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_Periodo: "yes", planificacion: idplanif}, "new_periodo", false, true);
    },
    showForEdit: function(pid){
    	ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Periodo: "yes", id: pid}, "edit_periodo", false);
    },
    restclick: function(dia, turno){
        var id = "#field_rest_"+dia+"_"+turno;
        if ($(id).val()=="x") $(id).val(" "); else $(id).val("x");
        var id = "#button_rest_"+dia+"_"+turno;
        if ($(id).val()=="x") $(id).val(" "); else $(id).val("x");
    },
    periodicidadClick: function(dia){
        var id = "#chk_periodicidad_"+dia;
        if ($(id).val()=="x") $(id).val(" "); else $(id).val("x");
        var id = "#btn_periodicidad_"+dia;
        if ($(id).val()=="x") $(id).val(" "); else $(id).val("x");
    },
    showEditPeriodicidad: function(pid){
    	ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Periodo_periodicidad: "yes", id: pid}, "edit_periodo_periodicidad", false);    	
    },
    guardarPeriodicidades: function(){
    	var planificacion = $("#field_Periodo_Planificacion").val();
    	var id = $("#field_Periodo_Id").val();
    	
    	var periodicidades = '';
        var cdp = $("#data_cant_dias_planificacion").val();
         
        for (var i=1; i<=cdp; i++){
                var v = $("#chk_periodicidad_" + i).val();
                if (v == "x") 
                    periodicidades = periodicidades + "1";
                else 
                    periodicidades = periodicidades + "0"; 
                if (i < cdp) periodicidades = periodicidades + ",";
        }

        this.flag_planificacion = planificacion;
        this.message = 'Periodicidades actualizadas satisfactoriamente';
        ancoragoh.server.periodos.updatePeriodicidades(id, periodicidades, this.event.fcomplete);
        ancoragoh.client.hidewin("edit_periodo_periodicidad");
        
    },
    cancelarPeriodicidades: function(){
    	ancoragoh.client.hidewin("edit_periodo_periodicidad");
    },
    showEditRestricciones: function(pid){
    	ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Periodo_restricciones: "yes", id: pid}, "edit_periodo_restricciones", false);    	
    },
    guardarRestricciones: function(){
    	var id = $("#field_Periodo_Id").val();
    	var planificacion = $("#field_Periodo_Planificacion").val();
    	var cant_dias = $("#field_Periodo_Cant_dias").val();
        var cant_turnos = $("#field_Periodo_Cant_turnos").val();
        
    	var restricciones = '';
         for(var i=1; i <= cant_dias; i++){
             for (var j=1; j<= cant_turnos; j++){
                 var v = $("#field_rest_" + i + "_" + j).val();
                 if (v == "x") restricciones = restricciones + "1"; else restricciones = restricciones + "0"; 
                 if (j < cant_turnos) restricciones = restricciones + ",";
             }
             if (i < cant_dias) restricciones = restricciones + ";";
         }
        
         alert(cant_dias);
         alert(cant_turnos);
    	

        this.flag_planificacion = planificacion;
        this.message = 'Restricciones actualizadas satisfactoriamente';
        ancoragoh.server.periodos.updateRestricciones(id, restricciones, this.event.fcomplete);
        ancoragoh.client.hidewin("edit_periodo_restricciones");
        
    },
    cancelarRestricciones: function(){
    	ancoragoh.client.hidewin("edit_periodo_restricciones");
    }
};
