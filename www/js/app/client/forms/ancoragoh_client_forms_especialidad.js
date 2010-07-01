/**
  * @name form_especialidad
  * @desc Formulario para insertar y editar especialidades
  */
ancoragoh.client.forms.especialidad = {
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified")
						ancoragoh.client.browsers.organizacion.refrescaEspecialidades();
						ancoragoh.client.showMessage(ancoragoh.client.forms.especialidad.event.message);
		},
		agregar: function(){
			var descripcion = $("#new_Especialidad_Descripcion").val();
			this.message = "Especialidad insertada satisfactoriamente";
			ancoragoh.server.especialidades.add(descripcion, this.fcomplete);	
            ancoragoh.client.hidewin("new_especialidad");
		},    
		modificar: function(){
			var id = $("#edit_Especialidad_Id").val();
			var descripcion = $("#edit_Especialidad_Descripcion").val();
			this.message = "Especialidad modificada satisfactoriamente";
			ancoragoh.server.especialidades.update(id, descripcion, this.fcomplete);	
			ancoragoh.client.hidewin("edit_especialidad");	
		},

		eliminar: function(){
			var id = $("#edit_Especialidad_Id").val();
			ancoragoh.server.especialidades.remove(id, this.fcomplete);
			this.message = "Especialidad eliminada satisfactoriamente";
			ancoragoh.client.hidewin("edit_especialidad");	
		},
		cancelar: function(){
			ancoragoh.client.hidewin("edit_especialidad");	
			ancoragoh.client.hidewin("new_especialidad");	
		}
    },
    showForNew: function(){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_Especialidad: "yes"}, "new_especialidad", false, true);
    },
    showForEdit: function(pid){
		ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Especialidad: "yes", id: pid}, "edit_especialidad", false, true);
    }
};
