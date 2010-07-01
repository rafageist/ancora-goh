ancoragoh.client.forms.clasifact = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						ancoragoh.client.browsers.desgloses.refrescarClasifAct();
						ancoragoh.client.showMessage(ancoragoh.client.forms.clasifact.event.message);
					}
		},
		agregar: function(){
			var descripcion = $("#field_ClasifAct_Descripcion").val();
			var duracion = $("#field_ClasifAct_Duracion").val();
			var gasto = $("#field_ClasifAct_Gasto").val();
			var prioridad = $("#field_ClasifAct_Prioridad").val();
			this.message = 'Clasificaci&oacute;n de actividad insertada satisfactoriamente';
			ancoragoh.server.clasifact.add(descripcion, duracion, gasto, prioridad, this.fcomplete);	
			ancoragoh.client.hidewin("new_clasifact");
		},
    
		modificar: function(){
				var descripcion = $("#field_ClasifAct_Descripcion").val();
				var duracion = $("#field_ClasifAct_Duracion").val();
				var gasto = $("#field_ClasifAct_Gasto").val();
				var prioridad = $("#field_ClasifAct_Prioridad").val();
				var id = $("#field_ClasifAct_Id").val();
				
				this.message = 'Clasificaci&oacute;n de actividad actualizada satisfactoriamente';
				ancoragoh.server.clasifact.update(id, descripcion, duracion, gasto, prioridad, this.fcomplete);
		        ancoragoh.client.hidewin("edit_clasifact");
		},
		eliminar: function(){
			var id = $("#field_ClasifAct_Id").val();
			this.message = 'Clasificaci&oacute;n de actividad eliminada satisfactoriamente';
			ancoragoh.server.clasifact.remove(id, this.fcomplete);
	        ancoragoh.client.hidewin("edit_clasifact");
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_clasifact");
			ancoragoh.client.hidewin("new_clasifact");
		}
    },
    showForNew: function(){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_new_clasifact: "yes"}, "new_clasifact", false, true);
    },
    showForEdit: function(pid){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_clasifact: "yes", id: pid}, "edit_clasifact", false, true);
    },
    subePrioridad: function (pid){
    	this.event.message = "Prioridad actualizada";
    	ancoragoh.server.clasifact.subePrioridad(pid, this.event.fcomplete)
    },
    bajaPrioridad: function (pid){
    	this.event.message = "Prioridad actualizada";
    	ancoragoh.server.clasifact.bajaPrioridad(pid, this.event.fcomplete)
    }
};
