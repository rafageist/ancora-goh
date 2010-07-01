ancoragoh.server.plantillas = {
    agregar: function(desglose, orden, clasificacion, exige_profe, exige_lugar, fcomplete){
         ajax_execute_event({
                    app_event_add_PlantillaDeActividad: "yes", 
                    field_PlantillaDeActividad_desglose: desglose,
                    field_PlantillaDeActividad_orden: orden,
                    field_PlantillaDeActividad_exige_profesor: exige_profe,
                    field_PlantillaDeActividad_exige_lugar: exige_lugar,
                    field_PlantillaDeActividad_clasificacion: clasificacion
                   }, fcomplete);
    },
    update: function(id, desglose, orden, clasificacion, exige_profe, exige_lugar, fcomplete){
         ajax_execute_event({
                    app_event_set_PlantillaDeActividad: "yes", 
                    field_PlantillaDeActividad_id: id,
                    field_PlantillaDeActividad_desglose: desglose,
                    field_PlantillaDeActividad_orden: orden,
                    field_PlantillaDeActividad_exige_profesor: exige_profe,
                    field_PlantillaDeActividad_exige_lugar: exige_lugar,
                    field_PlantillaDeActividad_clasificacion: clasificacion
                   }, fcomplete);
    },
    remove: function (id, fcomplete){
        ajax_execute_event({
                   app_event_del_PlantillaDeActividad: "yes", 
                   field_PlantillaDeActividad_id: id
                  }, fcomplete);
    },
    subePlantilla: function(id, fcomplete){
         ajax_execute_event({
                   app_event_custom_SubePlantilla: "yes", 
                   plantilla: id
                  }, fcomplete);
    },
    bajaPlantilla: function(id, fcomplete){
         ajax_execute_event({
                   app_event_custom_BajaPlantilla: "yes", 
                   plantilla: id
                  }, fcomplete);
    }
};