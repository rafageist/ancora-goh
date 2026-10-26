-- expect: pass
-- refs: generador/quita_segun_capacidad.sql, quita_segun_prohibidos.sql; recurso/asignacion/lugar_fijo.sql;
--       generador/generar.sql (lugar_fijo preferred); legacy-parity-inventory.md PAR-060..PAR-062
-- Room eligibility in the generator:
-- * capacity: a room is kept when capacidad = 0 (no limit) or capacidad >=
--   the summed enrolment (matricula) of the group's brigades;
-- * forbidden access (acceso_prohibido) removes the room for that group only;
-- * a fixed room (lugar_fijo) of the group is chosen first when it is among
--   the template's eligible, free rooms.
\ir ../fixtures/base.sql
\ir ../fixtures/two_templates.sql
UPDATE brigada SET matricula = 30;
UPDATE lugar SET capacidad = 25 WHERE id = 'L1';
SELECT tst.check(quita_segun_capacidad('{B1}', '{L1,L2}') = '{L2}', 'room below enrolment removed');
SELECT tst.check(quita_segun_capacidad('{B1,B2}', '{L1,L2}') IS NULL, 'enrolment of all brigades is summed (60): no room left');
UPDATE lugar SET capacidad = 0 WHERE id = 'L1';
SELECT tst.check(quita_segun_capacidad('{B1,B2}', '{L1,L2}') = '{L1}', 'capacity 0 means no limit');
UPDATE brigada SET matricula = 20; UPDATE lugar SET capacidad = 50;
INSERT INTO acceso_prohibido (grupo, lugar) VALUES ('G1', 'L1');
SELECT tst.check(quita_segun_prohibidos('{G1}', '{L1,L2}') = '{L2}' AND quita_segun_prohibidos('{G2}', '{L1,L2}') = '{L1,L2}', 'forbidden for G1 only');
DELETE FROM acceso_prohibido;
INSERT INTO lugar_para_actividad (plantilla, lugar, prioridad) VALUES ('T1', 'L2', 1);
INSERT INTO lugar_fijo (grupo, lugar) VALUES ('G2', 'L2');
SELECT generar('E', 'N', 'PER', 1, false, 0, false, false);
SELECT tst.check((SELECT lugar FROM actividad WHERE plantilla = 'T1' AND grupo = 'G2') = 'L2', 'fixed room chosen for G2');
SELECT tst.check((SELECT lugar FROM actividad WHERE plantilla = 'T1' AND grupo = 'G1') = 'L1', 'G1 without fixed room gets L1');
