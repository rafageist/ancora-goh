-- expect: pass
-- refs: #22; valor_restriccion_generica.sql, dame_clasif_de_asignaturas.sql, dame_costo_trasladar_recurso.sql
-- The three functions with variable/column name collisions work with the
-- server's default plpgsql.variable_conflict (= error), each through its own
-- "#variable_conflict use_variable" directive, and no other function needs one.
\ir ../fixtures/base.sql
SELECT tst.check(current_setting('plpgsql.variable_conflict') = 'error', 'server default plpgsql.variable_conflict is in effect');

SELECT tst.check(array_agg(n.nspname || '.' || p.proname ORDER BY n.nspname, p.proname)
                 = ARRAY['public.dame_clasif_de_asignaturas', 'public.dame_costo_trasladar_recurso', 'public.valor_restriccion_generica'],
                 'exactly the three affected functions carry the directive')
  FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
 WHERE p.prosrc ~ '^\s*#variable_conflict use_variable';

-- every object type of the generic evaluator (free cells: no restriction rows)
SELECT tst.check(valor_restriccion_generica(t, o, 'PER', 1, 1, '*') = 0, 'generic evaluator, type ' || t)
  FROM (VALUES ('PERIODO', 'PER'), ('ASIGNATURA', 'A'), ('BRIGADA', 'B1'), ('CLASIFACT', 'C1'),
               ('ESPECIALIDAD', 'E'), ('LUGAR', 'L1'), ('OTRORECURSO', 'R1'), ('PROFESOR', 'TA')) v(t, o);

-- the wrappers called by posible_inicio and the generator
SELECT tst.check(valor_restriccion_lugar('L1', 'PER', 1, 1) = 0, 'place wrapper');
SELECT tst.check(valor_restriccion_brigada('B1', 'PER', 1, 1) = 0, 'brigade wrapper');

-- generar() step 1 (generar.sql:230): classifications of a subject in a period
SELECT tst.check(dame_clasif_de_asignaturas('A', 'PER') = ARRAY['C1']::varchar[], 'classifications of subject A in PER');

-- HISTORICAL: with use_variable the period lookup "WHERE id = idperiodo" compares
-- the still-NULL variable "id", finds no period and the cost is always 0 (#22 notes).
SELECT tst.check(dame_costo_trasladar_recurso('R1', 'L1', 'PER', 1, 2) = 0, 'HISTORICAL: transfer cost evaluates (always 0)');
