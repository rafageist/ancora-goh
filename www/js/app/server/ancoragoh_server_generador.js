ancoragoh.server.generador = {
		
		generar: function(idesp, idnivel, idperiodo, pforma, pparche, pcompactar, pparche1, pbuscalugar, fcomplete){
			ajax_execute_event({
		        app_event_generar: "yes", 
		        esp: idesp,
		        nivel: idnivel,
		        periodo: idperiodo,
		        forma: pforma,
		        parche: pparche,
		        compactar: pcompactar,
		        parche1: pparche1,
		        buscalugar: pbuscalugar		        
		      }, fcomplete);
		},
		cancelar: function(idesp, idnivel, idperiodo, fcomplete){
			ajax_execute_event({
				app_event_cancelar_generacion: "yes",
				esp: idesp,
				nivel: idnivel,
				periodo: idperiodo
			},fcomplete);
		}
};