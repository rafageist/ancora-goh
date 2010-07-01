ancoragoh.client.forms = {

		nuevaClasifAct: function(){
            showInContent("index.php?execute&clasif_act&agregar");
        },
		editClasifAct: function(id){
            showInContent("index.php?execute&clasif_act&editar&id=" + id);
        },
        nuevoProfesor: function(){
            showDataForm("index.php?execute&profesores&agregar");
        },
        editProfesor: function(id){
            showDataForm("index.php?execute&profesores&editar&id=" + id);
        },
        nuevoLugar: function(){
            showDataForm("index.php?execute&lugares&agregar");
        },
        editLugar: function(id){
            showDataForm("index.php?execute&lugares&editar&id=" + id);
        },
        nuevoOtroRecurso: function(){
            showDataForm("index.php?execute&otros_recursos&agregar");
        },
        editOtroRecurso: function(id){
            showDataForm("index.php?execute&otros_recursos&editar&id=" + id);
        },
        editDesglose: function(id){
        	showDataForm("index.php?execute",{app_event_custom_getui_DesglosePropiedades: "yes", iddesglose: id});
        },
        nuevoGrupo: function(idclasif, idesp, idnivel){
           showDataForm("index.php?execute",{clasificacion: idclasif, especialidad: idesp, nivel: idnivel, app_event_custom_getui_NuevoGrupo:"yes"}); 
        },
        editarGrupo: function(id){
            showDataForm("index.php?execute",{grupo: id, app_event_custom_getui_EditarGrupo:"yes"}); 
        },
		nuevoLugarParaActividad: function(idplantilla){
			showDataForm("index.php?execute",{plantilla: idplantilla, app_event_custom_getui_NuevoLugarParaActividad:"yes"});
		}
};