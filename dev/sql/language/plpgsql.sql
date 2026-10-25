--  Lenguage procedural que se va a utilizar

-- PL/pgSQL is built in since PostgreSQL 9.0 and owned by the plpgsql extension;
-- the historical DROP/CREATE LANGUAGE 'plpgsql' fails on modern servers (#8).
CREATE EXTENSION IF NOT EXISTS plpgsql;
