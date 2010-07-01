 -- CONOCER RESTRICCIONES HEREDADAS
    CREATE or replace function valor_restriccion_heredada (tipo_hijo VARCHAR, id_hijo VARCHAR, idperiodo VARCHAR, pdia INTEGER, pturno INTEGER) RETURNS INTEGER as
    $$
        DECLARE th VARCHAR; 
            r record;
            v INTEGER;
            r1 record;
            otroid VARCHAR;
        BEGIN
            th := upper(tipo_hijo);

            -- ANALIZANDO LOS PADRES
            -- si algun padre est?estringido de cualquiera de las formas
            -- entonces el hijo tambien lo esta
            
            FOR r IN SELECT * FROM herencia_de_restricciones 
                WHERE activa = TRUE AND (dame_tipo_hijo_hrt(id) = th) 
                    AND ((hijo_brigada = id_hijo AND th = 'BRIGADA') 
                        OR (hijo_profesor = id_hijo AND th = 'PROFESOR')
                        OR (hijo_lugar = id_hijo AND th = 'LUGAR')
                        OR (hijo_otro_recurso = id_hijo AND th = 'OTRORECURSO')
                        )
                        LOOP

                otroid := iif(th = 'BRIGADA', r.padre_brigada, '');
                otroid := iif(th = 'PROFESOR', r.padre_profesor, '');
                otroid := iif(th = 'LUGAR', r.padre_lugar, '');
                otroid := iif(th = 'OTRORECURSO', r.padre_otro_recurso, '');
                
                v := valor_restriccion_generica(dame_tipo_padre_hrt(r.id), otroid, idperiodo, pdia, pturno, '*');

                IF v > 0 THEN

                    SELECT * INTO r1 FROM excepcion_de_hrt WHERE herencia = r.id AND periodo = idperiodo AND dia = pdia AND turno = pturno;

                    IF NOT FOUND THEN
                        RETURN v;
                    END IF;
                    
                END IF;

            END LOOP;

            -- ANALIZANDO LOS HIJOS
            -- si algn hijo esta restringido por actividad entonces el padre esta
            -- restringido
            
            FOR r IN SELECT * FROM herencia_de_restricciones 
                WHERE (dame_tipo_padre_hrt(id) = th) 
                    AND ((padre_brigada = id_hijo AND th = 'BRIGADA') 
                        OR (padre_profesor = id_hijo AND th = 'PROFESOR')
                        OR (padre_lugar = id_hijo AND th = 'LUGAR')
                        OR (padre_otro_recurso = id_hijo AND th = 'OTRORECURSO')
                        )
                        LOOP

                otroid := iif(th = 'BRIGADA', r.hijo_brigada, '');
                otroid := iif(th = 'PROFESOR', r.hijo_profesor, '');
                otroid := iif(th = 'LUGAR', r.hijo_lugar, '');
                otroid := iif(th = 'OTRORECURSO', r.hijo_otro_recurso, '');
                
                v := valor_restriccion_generica(dame_tipo_hijo_hrt(r.id), otroid, idperiodo, pdia, pturno, 'ACTIVIDAD');

                IF v > 0 THEN

                    SELECT * INTO r1 FROM excepcion_de_hrt WHERE herencia = r.id AND periodo = idperiodo AND dia = pdia AND turno = pturno;

                    IF NOT FOUND THEN
                        RETURN v;
                    END IF;
                    
                END IF;

            END LOOP;

            RETURN 0;
        END;
    $$
    LANGUAGE 'plpgsql';