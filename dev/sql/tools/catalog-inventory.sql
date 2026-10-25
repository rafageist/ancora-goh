-- Object inventory of an Áncora GOH database, one row per object:
--   kind <TAB> identity <TAB> md5 of the normalized definition
-- Normalization: lower case, whitespace collapsed, and the schema qualifier
-- "public." removed, so formatting-only differences compare equal.
-- Used by compare-catalog.sh; run with psql -X -At -F $'\t'.
\set QUIET on
WITH ns AS (
  SELECT oid, nspname FROM pg_namespace
  WHERE nspname IN ('public','seguridad','generador','matrix','prioriza','tiempo','util','portal')
), norm AS (
  SELECT kind, ident, def FROM (
    SELECT 'schema'::text AS kind, nspname::text AS ident, ''::text AS def FROM ns
    UNION ALL
    SELECT CASE c.relkind WHEN 'v' THEN 'view' ELSE 'table' END,
           ns.nspname || '.' || c.relname,
           CASE c.relkind WHEN 'v' THEN pg_get_viewdef(c.oid)
           ELSE (SELECT string_agg(a.attname || ' ' || format_type(a.atttypid, a.atttypmod)
                                   || CASE WHEN a.attnotnull THEN ' not null' ELSE '' END
                                   || coalesce(' default ' || pg_get_expr(d.adbin, d.adrelid), ''),
                                   ', ' ORDER BY a.attname)
                 FROM pg_attribute a LEFT JOIN pg_attrdef d ON d.adrelid = a.attrelid AND d.adnum = a.attnum
                 WHERE a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped) END
    FROM pg_class c JOIN ns ON ns.oid = c.relnamespace WHERE c.relkind IN ('r','v','p')
    UNION ALL
    SELECT 'column', ns.nspname || '.' || c.relname || '.' || a.attname,
           format_type(a.atttypid, a.atttypmod)
             || CASE WHEN a.attnotnull THEN ' not null' ELSE '' END
             || coalesce(' default ' || pg_get_expr(d.adbin, d.adrelid), '')
    FROM pg_attribute a JOIN pg_class c ON c.oid = a.attrelid JOIN ns ON ns.oid = c.relnamespace
    LEFT JOIN pg_attrdef d ON d.adrelid = a.attrelid AND d.adnum = a.attnum
    WHERE c.relkind IN ('r','p') AND a.attnum > 0 AND NOT a.attisdropped
    UNION ALL
    SELECT 'constraint:' || co.contype::text, ns.nspname || '.' || c.relname || ':' || pg_get_constraintdef(co.oid), ''
    FROM pg_constraint co JOIN pg_class c ON c.oid = co.conrelid JOIN ns ON ns.oid = c.relnamespace
    UNION ALL
    SELECT 'type', ns.nspname || '.' || t.typname,
           (SELECT string_agg(a.attname || ' ' || format_type(a.atttypid, a.atttypmod), ', ' ORDER BY a.attnum)
            FROM pg_attribute a WHERE a.attrelid = t.typrelid AND a.attnum > 0)
    FROM pg_type t JOIN ns ON ns.oid = t.typnamespace JOIN pg_class c ON c.oid = t.typrelid
    WHERE t.typtype = 'c' AND c.relkind = 'c'
    UNION ALL
    SELECT 'function', ns.nspname || '.' || p.proname || '(' || pg_get_function_identity_arguments(p.oid) || ')',
           pg_get_functiondef(p.oid)
    FROM pg_proc p JOIN ns ON ns.oid = p.pronamespace
    WHERE p.prokind IN ('f','p') AND p.proname NOT LIKE 'pldbg\_%' AND p.proname <> 'plpgsql_oid_debug'
    UNION ALL
    SELECT 'trigger', ns.nspname || '.' || c.relname || ':' || t.tgname, pg_get_triggerdef(t.oid)
    FROM pg_trigger t JOIN pg_class c ON c.oid = t.tgrelid JOIN ns ON ns.oid = c.relnamespace
    WHERE NOT t.tgisinternal
    UNION ALL
    SELECT 'rule', ns.nspname || '.' || c.relname || ':' || r.rulename, pg_get_ruledef(r.oid)
    FROM pg_rewrite r JOIN pg_class c ON c.oid = r.ev_class JOIN ns ON ns.oid = c.relnamespace
    WHERE r.rulename <> '_RETURN'
  ) x
)
SELECT kind, ident,
       md5(regexp_replace(replace(lower(coalesce(def, '')), 'public.', ''), '\s+', ' ', 'g'))
FROM norm ORDER BY kind, ident;
