-- expect: pass
-- refs: tiempo/restriccion/valor_restriccion_generica.sql; assessment 6.1; sql-reconciliation.md §6; #22
-- Restriction evaluation with the server's default settings (no session
-- plpgsql.variable_conflict override). Until #22 the parameter "id" collided
-- with column "id" (42702, column reference "id" is ambiguous); the function
-- now carries "#variable_conflict use_variable" and evaluates to 0 or 1.
\ir ../fixtures/base.sql
SELECT tst.check(valor_restriccion_profesor('TA', 'PER', 1, 1) IN (0, 1), 'teacher restriction evaluates');
