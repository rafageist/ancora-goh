CREATE OR REPLACE RULE crea_restriccion_real_de_periodo AS
    ON INSERT TO restriccion_de_periodo 
    DO ALSO SELECT actualiza_restriccion_real(NEW.periodo, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_periodo');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_periodo AS
    ON UPDATE TO restriccion_de_periodo 
    DO ALSO SELECT actualiza_restriccion_real(NEW.periodo, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_periodo');

CREATE OR REPLACE RULE crea_restriccion_real_de_asignatura AS
    ON INSERT TO restriccion_de_asignatura 
    DO ALSO SELECT actualiza_restriccion_real(NEW.asignatura, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_asignatura');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_asignatura AS
    ON UPDATE TO restriccion_de_asignatura 
    DO ALSO SELECT actualiza_restriccion_real(NEW.asignatura, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_asignatura');

CREATE OR REPLACE RULE crea_restriccion_real_de_brigada AS
    ON INSERT TO restriccion_de_brigada 
    DO ALSO SELECT actualiza_restriccion_real(NEW.brigada, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_brigada');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_brigada AS
    ON UPDATE TO restriccion_de_brigada 
    DO ALSO SELECT actualiza_restriccion_real(NEW.brigada, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_brigada');

CREATE OR REPLACE RULE crea_restriccion_real_de_especialidad AS
    ON INSERT TO restriccion_de_especialidad 
    DO ALSO SELECT actualiza_restriccion_real(NEW.especialidad, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_especialidad');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_especialidad AS
    ON UPDATE TO restriccion_de_especialidad 
    DO ALSO SELECT actualiza_restriccion_real(NEW.especialidad, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_especialidad');

-- Restricciones de profesores
CREATE OR REPLACE RULE crea_restriccion_real_de_profesor AS
    ON INSERT TO restriccion_de_profesor
    DO ALSO SELECT actualiza_restriccion_real(NEW.profesor, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_profesor');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_profesor AS
    ON UPDATE TO restriccion_de_profesor 
    DO ALSO SELECT actualiza_restriccion_real(NEW.profesor, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_profesor');


CREATE OR REPLACE RULE crea_restriccion_real_de_lugar AS
    ON INSERT TO restriccion_de_lugar
    DO ALSO SELECT actualiza_restriccion_real(NEW.lugar, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_lugar');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_lugar AS
    ON UPDATE TO restriccion_de_lugar 
    DO ALSO SELECT actualiza_restriccion_real(NEW.lugar, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_lugar');

-- Restricciones de otros recursos

CREATE OR REPLACE RULE crea_restriccion_real_de_otro_recurso AS
    ON INSERT TO restriccion_de_otro_recurso
    DO ALSO SELECT actualiza_restriccion_real(NEW.otro_recurso, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_otro_recurso');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_otro_recurso AS
    ON UPDATE TO restriccion_de_otro_recurso
    DO ALSO SELECT actualiza_restriccion_real(NEW.otro_recurso, NEW.actividad, NEW.id, NEW.periodo, NEW.dia, NEW.turno, NEW.por_actividad, 'restriccion_real_de_otro_recurso');

-- Restricciones de clasif act

CREATE OR REPLACE RULE crea_restriccion_real_de_clasif_act AS
    ON INSERT TO restriccion_de_clasif_act
    DO ALSO SELECT actualiza_restriccion_real(NEW.clasificacion, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_clasif_act');

CREATE OR REPLACE RULE actualiza_restriccion_real_de_clasif_act AS
    ON UPDATE TO restriccion_de_clasif_act
    DO ALSO SELECT actualiza_restriccion_real(NEW.clasificacion, null, NEW.id, NEW.periodo, NEW.dia, NEW.turno, false, 'restriccion_real_de_clasif_act');


/** restricciones por actividad de brigadas */

CREATE OR REPLACE RULE actualiza_por_actividad_en_nueva_restriccion_de_brigada AS
    ON INSERT TO restriccion_por_actividad_de_brigada
    DO ALSO 
        UPDATE restriccion_de_brigada SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;

CREATE OR REPLACE RULE actualiza_por_actividad_en_restriccion_de_brigada AS
    ON UPDATE TO restriccion_por_actividad_de_brigada
    DO ALSO (
        UPDATE restriccion_de_brigada SET por_actividad = FALSE WHERE id = OLD.restriccion_nominal;
        UPDATE restriccion_de_brigada SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;
    );

    
/** restricciones por actividad de profesores */

CREATE OR REPLACE RULE actualiza_por_actividad_en_nueva_restriccion_de_profesor AS
    ON INSERT TO restriccion_por_actividad_de_profesor
    DO ALSO UPDATE restriccion_de_profesor SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;

CREATE OR REPLACE RULE actualiza_por_actividad_en_restriccion_de_profesor AS
    ON UPDATE TO restriccion_por_actividad_de_profesor
    DO ALSO ( 
        UPDATE restriccion_de_profesor SET por_actividad = FALSE WHERE id = OLD.restriccion_nominal;
        UPDATE restriccion_de_profesor SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;
    );

/** restricciones por actividad de lugares */

CREATE OR REPLACE RULE actualiza_por_actividad_en_nueva_restriccion_de_lugar AS
    ON INSERT TO restriccion_por_actividad_de_lugar
    DO ALSO UPDATE restriccion_de_lugar SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;

CREATE OR REPLACE RULE actualiza_por_actividad_en_restriccion_de_lugar AS
    ON UPDATE TO restriccion_por_actividad_de_lugar
    DO ALSO ( 
        UPDATE restriccion_de_lugar SET por_actividad = FALSE WHERE id = OLD.restriccion_nominal;
        UPDATE restriccion_de_lugar SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;
    );

/** restricciones por actividad de otros recursos */

CREATE OR REPLACE RULE actualiza_por_actividad_en_nueva_restriccion_de_otro_recurso AS
    ON INSERT TO restriccion_por_actividad_de_otro_recurso
    DO ALSO UPDATE restriccion_de_otro_recurso SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;

CREATE OR REPLACE RULE actualiza_por_actividad_en_restriccion_de_otro_recurso AS
    ON UPDATE TO restriccion_por_actividad_de_otro_recurso
    DO ALSO ( 
        UPDATE restriccion_de_otro_recurso SET por_actividad = FALSE WHERE id = OLD.restriccion_nominal;
        UPDATE restriccion_de_otro_recurso SET por_actividad = TRUE WHERE id = NEW.restriccion_nominal;
    );

CREATE OR REPLACE RULE actualiza_restricciones_por_actividad AS
    ON INSERT TO actividad
    DO ALSO 
    SELECT restringe_segun_actividad(NEW.id, NEW.plantilla, NEW.profesor, NEW.lugar, NEW.dia, NEW.turno, NEW.grupo);
        
CREATE OR REPLACE RULE actualiza_restricciones_por_uso_de_otro_recurso AS
    ON INSERT TO otros_recursos_usados
    DO ALSO 
    SELECT restringe_por_uso_de_otro_recurso(NEW.actividad, NEW.otro_recurso);



