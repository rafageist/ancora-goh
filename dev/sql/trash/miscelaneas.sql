CREATE OR REPLACE FUNCTION iif(BOOLEAN, anyelement, anyelement) returns anyelement AS
$body$ SELECT case $1 when true then $2 else $3 end $body$
LANGUAGE sql IMMUTABLE;
