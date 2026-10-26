-- Grants for the engine worker role of one tenant database (issue #95).
--
--   psql -v worker=<login role> -d <tenant database> -f worker.sql
--
-- The worker opens, marks and closes job workspaces, forwards the soft
-- cancellations of its jobs and runs the cleanup. It has no privilege on any
-- workspace: it is a member of each job role WITH INHERIT FALSE and must
-- SET ROLE to it. It has none on the engine templates or on Drupal's tables.
GRANT CONNECT ON DATABASE :"DBNAME" TO :"worker";
GRANT USAGE ON SCHEMA scheduler_control TO :"worker";
GRANT EXECUTE ON FUNCTION scheduler_control.open_job_workspace(text), scheduler_control.mark_workspace(text, text),
  scheduler_control.close_job_workspace(text, integer), scheduler_control.worker_cleanup() TO :"worker";
GRANT INSERT ON scheduler_control.cancel_request TO :"worker";
