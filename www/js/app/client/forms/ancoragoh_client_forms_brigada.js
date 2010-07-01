ancoragoh.client.forms.brigada = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified"){
						ancoragoh.client.browsers.organizacion.refrescarCentro();
						ancoragoh.client.showMessage(ancoragoh.client.forms.brigada.event.message);
					}
		},
		agregar: function(){
			var descripcion = $("#field_Brigada_Descripcion").val();
		    var matricula =  $("#field_Brigada_Matricula").val();
		    var especialidad  =  ancoragoh.client.browsers.organizacion.flag_especialidad; 
		    var nivel  =  ancoragoh.client.browsers.organizacion.flag_nivel;
		    this.message = "Brigada insertada satisfactoriamente";
			ancoragoh.server.brigadas.add(descripcion, matricula, especialidad, nivel, this.fcomplete);	
		    ancoragoh.client.hidewin("new_brigada");
		},
    
		modificar: function(){
			var id = $("#field_Brigada_Id").val();
			var descripcion = $("#field_Brigada_Descripcion").val();
		    var matricula =  $("#field_Brigada_Matricula").val();
		    var especialidad  =  ancoragoh.client.browsers.organizacion.flag_especialidad; 
		    var nivel  =  ancoragoh.client.browsers.organizacion.flag_nivel;
	        
			ancoragoh.server.brigadas.update(id,descripcion,matricula,especialidad,nivel, this.fcomplete);	
			this.message = "Brigada modificada satisfactoriamente";
			ancoragoh.client.hidewin("edit_brigada");
		},

		eliminar: function(){
			var id = $("#field_Brigada_Id").val();
			ancoragoh.server.brigadas.remove(id, this.fcomplete);
			this.message = "Brigada eliminada satisfactoriamente";
			ancoragoh.client.hidewin("edit_brigada");
		},

		cancelar: function(){
			ancoragoh.client.hidewin("new_brigada");
			ancoragoh.client.hidewin("edit_brigada");
		}
    },
    showForNew: function(){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_Brigada: "yes"}, "new_brigada", false, true);
    },
    showForEdit: function(pid){
    	ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Brigada: "yes", id: pid}, "edit_brigada", false, true);
    }
};
