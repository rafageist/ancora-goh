/**
  * @name form_nivel
  * @desc Formulario para insertar y editar niveles
  */
ancoragoh.client.forms.nivel = {	
    event: {
		message: "",
		fcomplete: function(res, status){
					if ( status == "success" || status == "notmodified")
						ancoragoh.client.browsers.desgloses.refrescaNiveles();
						ancoragoh.client.showMessage(ancoragoh.client.forms.nivel.event.message);
		},
		agregar: function(){
			var descripcion = $("#field_Nivel_Descripcion").val();
			this.message = "Nivel insertado satisfactoriamente";
			ancoragoh.server.niveles.add(descripcion, this.fcomplete);	
            ancoragoh.client.hidewin("new_nivel");
		},
		
		modificar: function(){
			var id = $("#field_Nivel_Id").val();
			var descripcion = $("#field_Nivel_Descripcion").val();
			this.message =  "Nivel modificado satisfactoriamente";
			ancoragoh.server.niveles.update(id,descripcion);	
			ancoragoh.client.hidewin("edit_nivel");
		},

		eliminar: function(){
			var id = $("#field_Nivel_Id").val();
			this.message = "Nivel eliminado satisfactoriamente";
			ancoragoh.server.niveles.remove(id, this.fcomplete);
			ancoragoh.client.hidewin("edit_nivel", this.fcomplete);
		},

		cancelar: function(){
			ancoragoh.client.hidewin("edit_nivel");
			ancoragoh.client.hidewin("new_nivel");
		}
    },
	
    showForNew: function(){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_new_Nivel: "yes"}, "new_nivel", false, true);
    },
	
    showForEdit: function(pid){
        ajax_load_htmlToWin("index.php?execute", {app_event_get_form_edit_Nivel: "yes", id: pid}, "edit_nivel", false, true);
    }
};
