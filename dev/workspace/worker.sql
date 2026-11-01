-- Grants for the engine worker role of one tenant database (issues #95, #96).
-- Included by install.sh --worker <login role> (psql variable "worker").
--
-- The worker opens, marks and closes job workspaces, forwards the soft
-- cancellations of its jobs and runs the cleanup, all through the SECURITY
-- DEFINER functions of scheduler_control. It has no privilege on any
-- workspace: it is a member of each job role WITH INHERIT FALSE and must
-- SET ROLE to it. It has none on the engine templates, on the control tables
-- or on Drupal's tables, no TEMPORARY and no CREATE anywhere.
GRANT CONNECT ON DATABASE :"DBNAME" TO :"worker";
GRANT USAGE ON SCHEMA scheduler_control TO :"worker";
GRANT EXECUTE ON FUNCTION scheduler_control.open_job_workspace(text), scheduler_control.mark_workspace(text, text),
  scheduler_control.close_job_workspace(text, integer), scheduler_control.worker_cleanup(),
  scheduler_control.request_cancel(text, varchar, varchar, varchar) TO :"worker";
