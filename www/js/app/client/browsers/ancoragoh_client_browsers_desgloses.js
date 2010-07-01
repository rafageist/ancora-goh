ancoragoh.client.browsers.desgloses = {
	flag_idplantilla: "",
	flag_idasig: "",
	flag_especialidad: "",
	flag_nivel: "",
	flag_planif: "",
	flag_showrecs: 1, // 1- profesores asignados, 2 - lugares asignados, 3 - otros recursos
	message: "",
	fcompleteLxact: function(res, status){
		if ( status == "success" || status == "notmodified"){
			ancoragoh.client.browsers.desgloses.showLugaresParaActividad();
			ancoragoh.client.showMessage(ancoragoh.client.browsers.desgloses.message);
		}
	},
	fcompleteDetalles: function(res, status){
		if ( status == "success" || status == "notmodified"){
			ancoragoh.client.browsers.desgloses.refrescaDesgloseDetalle();
			if (ancoragoh.client.browsers.desgloses.message != "") 
				ancoragoh.client.showMessage(ancoragoh.client.browsers.desgloses.message);
		}
	},
	show: function(){
		ancoragoh.client.loadHTMLToWin({app_event_get_browser_Desgloses: "yes"}, "desgloses", true, false);
	},
	
	
	showLeft: function(){
		$("#treeDesglosesAsig").fadeIn("slow");
		$("#div_recursos").fadeOut("slow");
		$("#btnShowAsignaturas").fadeOut("slow");
		$("#btnShowRecursos").fadeIn("slow");
	},
	showRight: function(){
		$("#treeDesglosesAsig").fadeOut("slow");
		$("#div_recursos").fadeIn("slow");
		$("#btnShowAsignaturas").fadeIn("slow");
		$("#btnShowRecursos").fadeOut("slow");
	},
	seleccionaPlanificacion: function (id){
		this.flag_planif = id;
		this.refrescaDesgloseDetalle();
	},
    seleccionaAsignatura: function(idasg, idesp, idnivel){
		this.seleccionaEspecialidad(idesp);
		this.seleccionaNivel(idnivel);
		this.flag_idasig = idasg;
		this.refrescaDesgloseDetalle();
    }, 
    seleccionaEspecialidad: function(id){
    	this.flag_especialidad = id;
    },
    seleccionaNivel: function(id){
    	this.flag_nivel = id;
    },
    refrescaDesgloseDetalle: function(){
    	var idasg = this.flag_idasig;
		var idplanif = this.flag_planif;
    	if (this.flag_idasig != "" && this.flag_idplanif != ""){
    		ajax_load_html("index.php?execute",{idasig: idasg, idplanificacion: idplanif, app_event_get_browser_DesgloseDetalles: "yes"}, "desglosesDetalles");
    	} else {
    		if (this.flag_idasig == "") ancoragoh.client.showMessage("Seleccione la asignatura",'info.png');
    		if (this.flag_planif == "") ancoragoh.client.showMessage("Seleccione la planifiaci&oacuten",'info.png');
    	}
    },    
    showAsignacionRecursos: function(idplantilla){
    	this.showRight();
    	if (idplantilla == null)
    		idplantilla = this.flag_idplantilla;
    	else
    		this.flag_idplantilla = idplantilla;
    	
    	ajax_load_html("index.php?execute", {plantilla: idplantilla, app_event_custom_getui_AsignacionRecursos:"yes"}, "div_recursos");
    },
    
    showProfesoresParaActividad: function (idplantilla){
    	this.showRight();
    	if (idplantilla == null)
    		idplantilla = this.flag_idplantilla;
    	else
    		this.flag_idplantilla = idplantilla;
    	
    	this.showAsignacionRecursos(idplantilla);
    	
        ajax_load_html("index.php?execute",{plantilla: idplantilla, app_event_custom_getui_ProfesoresAsignados:"yes"}, "div_asignacion_recursos");
    },
    showLugaresParaActividad: function (idplantilla){
    	this.showRight();
    	if (idplantilla == null)
    		idplantilla = this.flag_idplantilla;
    	else
    		this.flag_idplantilla = idplantilla;
    	
    	this.showAsignacionRecursos(idplantilla);
    	
        ajax_load_html("index.php?execute",{plantilla: idplantilla, app_event_custom_getui_LugaresAsignados:"yes"}, "div_asignacion_recursos");
    },
    
    showOtrosRecursosParaActividad: function (idplantilla){
    	//this.showRight();
    	if (idplantilla == null)
    		idplantilla = this.flag_idplantilla;
    	else
    		this.flag_idplantilla = idplantilla;
    	
    	//this.showAsignacionRecursos(idplantilla);
    	
    	ancoragoh.client.loadHTMLToWin({plantilla: idplantilla, app_event_custom_getui_OtrosRecursosAsignados:"yes"}, "asignacion_de_otros_recursos", false, false);
        //ajax_load_html("index.php?execute",{plantilla: idplantilla, app_event_custom_getui_OtrosRecursosAsignados:"yes"}, "div_asignacion_recursos");
    },
	editarPrioridadesLxAct: function(idplantilla, idlugar, iddiv){
		ajax_load_html("index.php?execute",{plantilla: idplantilla, lugar: idlugar, app_event_custom_getui_EditarPrioridadLxact: "yes"}, iddiv);
	},
	actualizaPrioridad: function(p, idlugar){
		var idplantilla = this.flag_idplantilla;
		this.message = "Prioridad actualizada correctamente"; 
		ancoragoh.server.recursos.actualizaPrioridadLxact(idplantilla, idlugar, p, this.fcompleteLxact);
        $("#lxact_prioridad").fadeOut("slow");
	},
    cancelarPrioridad: function(){
        $("#lxact_prioridad").fadeOut("slow");
        $('.tr_lxact').css("background","white");
        $('.tr_lxact').css("color","black");
    },
    bajaPlantilla: function (id){
    	this.message = "";
        ancoragoh.server.plantillas.bajaPlantilla(id, this.fcompleteDetalles);
    },
    subePlantilla: function (id){
    	this.message = "";
        ancoragoh.server.plantillas.subePlantilla(id, this.fcompleteDetalles);
    },
    refrescarAsignaturas: function(){
    	$("#desglosesDetalles").fadeIn("slow");
    	$("#desglosesSelectPlanificacion").fadeIn("slow");
    	ajax_load_html("index.php?execute",{app_event_get_minibrowser_asignaturas: "yes"}, "treeDesglosesAsig");
    },
    refrescarClasifAct: function(){
    	$("#desglosesDetalles").fadeOut("slow");
    	$("#desglosesSelectPlanificacion").fadeOut("slow");
    	ajax_load_html("index.php?execute",{app_event_get_minibrowser_clasifact: "yes"}, "treeDesglosesAsig");
    }
};