-- Least-privilege grants for the portal runtime role (psql variable :app_role).
-- Run as the owner after schema.sql:
--   psql -v app_role=tt_control_app -f grants.sql
-- The runtime role cannot create, alter or drop anything, cannot change the
-- audit trail after the fact (INSERT only) and cannot read schema_version
-- history beyond SELECT.

REVOKE ALL ON SCHEMA portal FROM PUBLIC;
GRANT USAGE ON SCHEMA portal TO :"app_role";
GRANT SELECT ON portal.schema_version TO :"app_role";
GRANT SELECT, INSERT, UPDATE, DELETE ON portal.access_request TO :"app_role";
GRANT SELECT, INSERT, UPDATE, DELETE ON portal.verification_token TO :"app_role";
GRANT SELECT, INSERT, UPDATE, DELETE ON portal.rate_limit TO :"app_role";
GRANT SELECT, INSERT ON portal.request_event TO :"app_role";
