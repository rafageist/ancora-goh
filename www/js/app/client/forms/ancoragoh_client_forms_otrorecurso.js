ancoragoh.client.forms.otrorecurso = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified")
						ancoragoh.client.browsers.recursos.refrescar();
						ancoragoh.client.showMessage(ancoragoh.client.forms.otrorecurso.event.message);
		},
		agregar: function(){
			var nombre = $("#field_OtroRecurso_Nombre").val();
			this.message = 'Recurso insertado satisfactoriamente';
			ancoragoh.server.otrosrecursos.add(nombre, this.fcomplete);	
			ancoragoh.client.hidewin("new_otrorecurso");
		},
    
		modificar: function(){
				var id = $("#field_OtroRecurso_Id").val();
				var nombre = $("#field_OtroRecurso_Nombre").val();
				this.message = 'Profesor modificado satisfactoriamente';
				ancoragoh.server.otrosrecursos.update(id, nombre, this.fcomplete);
		        ancoragoh.client.hidewin("edit_otrorecurso");
		},
		eliminar: function(){
			var id = $("#field_OtroRecurso_Id").val();
			this.message = 'Recurso eliminado satisfactoriamente';
			ancoragoh.server.otrosrecursos.remove(id, this.fcomplete);
	        ancoragoh.client.hidewin("edit_otrorecurso");
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_otrorecurso");
			ancoragoh.client.hidewin("new_otrorecurso");
		}
    },
    showForNew: function(){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_new_otrorecurso: "yes"}, "new_otrorecurso", false, true);
    },
    showForEdit: function(pid){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_otrorecurso: "yes", id: pid}, "edit_otrorecurso", false, true);
    }
};
