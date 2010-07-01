ancoragoh.client.forms.profesor = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						ancoragoh.client.browsers.recursos.refrescar();
						ancoragoh.client.showMessage(ancoragoh.client.forms.profesor.event.message);
					}
		},
		agregar: function(){
			var nombre = $("#field_Profesor_Nombre").val();
			this.message = 'Profesor insertado satisfactoriamente';
			ancoragoh.server.profesores.add(nombre, this.fcomplete);	
			ancoragoh.client.hidewin("new_profesor");
		},
    
		modificar: function(){
				var id = $("#field_Profesor_Id").val();
				var nombre = $("#field_Profesor_Nombre").val();
				this.message = 'Profesor modificado satisfactoriamente';
				ancoragoh.server.profesores.update(id, nombre, this.fcomplete);
		        ancoragoh.client.hidewin("edit_profesor");
		},
		eliminar: function(){
			var id = $("#field_Profesor_Id").val();
			this.message = 'Profesor eliminado satisfactoriamente';
			ancoragoh.server.profesores.remove(id, this.fcomplete);
	        ancoragoh.client.hidewin("edit_profesor");
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_profesor");
			ancoragoh.client.hidewin("new_profesor");
		}
    },
    showForNew: function(){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_new_profesor: "yes"}, "new_profesor", false, true);
    },
    showForEdit: function(pid){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_profesor: "yes", id: pid}, "edit_profesor", false, true);
    }
};
