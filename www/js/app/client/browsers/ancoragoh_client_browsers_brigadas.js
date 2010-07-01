ancoragoh.client.browsers.brigadas = {
        showBrigadasBy: function(idesp, idnivel){
            application.browsers.showBrigadasBy(idesp, idnivel, 'div_brgs');
            $("#data_idesp_selected").val(idesp);
            $("#data_idnivel_selected").val(idnivel);
            $("#brggrpselector").fadeIn("fast");
        },
        showBrigadas: function(){
            var idesp = $("#data_idesp_selected").val();
            var idnivel = $("#data_idnivel_selected").val();
            application.browsers.showBrigadasBy(idesp, idnivel, 'div_brgs');
        },
        showGrupos: function(){
            var idesp = $("#data_idesp_selected").val();
            var idnivel = $("#data_idnivel_selected").val();
            application.browsers.showGruposBy(idesp, idnivel, 'div_brgs');
        },
        selectPlanificacion: function(id, control){
            $("#data_idplanif_selected").val(id);
            
            var sp = $("#data_controlplanif_selected").val();
            
            $("#" + control).css("background","#AAAAAA");
            $("#" + control).css("border","3px dashed #AAAAAA");
            $("#" + control).css("color","white");
            
            $("#" + sp).css("background","white");
            $("#" + sp).css("border","0px");
            $("#" + sp).css("color","black");
            
            $("#data_controlplanif_selected").val(control);
            
            showGrupos();
        }
}