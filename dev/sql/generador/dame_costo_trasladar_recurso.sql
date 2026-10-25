
-- SELECT dame_costo_trasladar_recurso('r1','l1','si',1,2);
CREATE OR REPLACE FUNCTION dame_costo_trasladar_recurso(idrecurso VARCHAR, idlugar VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS BIGINT as
$$
#variable_conflict use_variable
-- #22: a name that is both a variable and a column resolves to the variable,
-- as PostgreSQL 8.4 (2010) always did; PostgreSQL >= 9.0 raises 42702 instead.
-- Here: variables "id" and "costo" vs. the "id" and "costo" columns.
    DECLARE i BIGINT; j BIGINT;
        costo BIGINT;
        rperiodo record;
        r record;
        id VARCHAR;
    BEGIN

        SELECT * INTO rperiodo FROM periodo WHERE id = idperiodo;

        costo := 0;

        IF rperiodo.cant_turnos > 0 THEN
            
            -- busco actividad anterior
            i := pturno - 1;
            LOOP
                SELECT actividad.lugar as lugar INTO r FROM otros_recursos_usados INNER JOIN actividad ON actividad.id = otros_recursos_usados.actividad WHERE actividad.dia = pdia AND actividad.turno = i AND dame_periodo_plantilla_de_actividad(actividad.plantilla) = idperiodo AND otros_recursos_usados.otro_recurso = idrecurso;

                IF FOUND THEN
                    id := r.lugar;
                    SELECT costo INTO r FROM costo_de_traslado WHERE origen = id AND destino = idlugar;
                    IF FOUND THEN
                        costo := r.costo;
                    END IF;
                END IF;

                EXIT WHEN i = 1;
                i := i -1;
            END LOOP;

            
            -- busco actividad siguiente
            i := pturno + 1;
            LOOP
                SELECT actividad.lugar as lugar INTO r FROM otros_recursos_usados INNER JOIN actividad ON actividad.id = otros_recursos_usados.actividad WHERE actividad.dia = pdia AND actividad.turno = i AND dame_periodo_plantilla_de_actividad(actividad.plantilla) = idperiodo AND otros_recursos_usados.otro_recurso = idrecurso;

                IF FOUND THEN
                    id := r.lugar;
                    SELECT costo INTO r FROM costo_de_traslado WHERE destino = id AND origen = idlugar;
                    IF FOUND THEN
                        costo := costo + r.costo;
                    END IF;
                END IF;

                EXIT WHEN i = rperiodo.cant_turnos;
                i := i + 1;
            END LOOP;
        END IF;
        RETURN costo;
    END;
$$
LANGUAGE 'plpgsql';