ancoragoh.client.forms.lugar = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified")
						ancoragoh.client.browsers.recursos.refrescar();
						ancoragoh.client.showMessage(ancoragoh.client.forms.lugar.event.message);
		},
		agregar: function(){
			var nombre = $("#field_Lugar_Nombre").val();
			var capacidad = $("#field_Lugar_Capacidad").val();
			this.message = 'Lugar insertado satisfactoriamente';
			ancoragoh.server.lugares.add(nombre, capacidad, this.fcomplete);	
			ancoragoh.client.hidewin("new_lugar");
		},
    
		modificar: function(){
				var id = $("#field_Lugar_Id").val();
				var nombre = $("#field_Lugar_Nombre").val();
				var capacidad = $("#field_Lugar_Capacidad").val();
				this.message = 'Lugar modificado satisfactoriamente';
				ancoragoh.server.lugares.update(id, nombre, capacidad, this.fcomplete);
		        ancoragoh.client.hidewin("edit_lugar");
		},
		eliminar: function(){
			var id = $("#field_Lugar_Id").val();
			this.message = 'Lugar eliminado satisfactoriamente';
			ancoragoh.server.lugares.remove(id, this.fcomplete);
	        ancoragoh.client.hidewin("edit_lugar");
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_lugar");
			ancoragoh.client.hidewin("new_lugar");
		}
    },
    showForNew: function(){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_new_lugar: "yes"}, "new_lugar", false, true);
    },
    showForEdit: function(pid){
        ancoragoh.client.loadHTMLToWin({app_event_get_form_edit_lugar: "yes", id: pid}, "edit_lugar", false, true);
    }
};
