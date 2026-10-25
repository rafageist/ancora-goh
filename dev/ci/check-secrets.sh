#!/usr/bin/env bash
# Credential hygiene guard for the current tree (issue #11).
# Fails when a tracked file reintroduces a plaintext credential in the places
# where Áncora GOH historically kept them. It checks the working tree only, not
# Git history, and it never prints matched values.
#
# Usage: dev/ci/check-secrets.sh   (run from any directory inside the repository)
set -u
cd "$(git rev-parse --show-toplevel)" || exit 2

# Historical evidence kept unchanged on purpose (see docs/modernization/security-privacy.md).
# These files are excluded from content checks, not from review.
ALLOW='^dev/backup/ancoragoh\.(sql|custom|tar)$'

fail=0
report() { # $1 = rule, $2 = file:line list (values are not shown)
  echo "FAIL [$1]"; sed 's/^/  /' <<<"$2"; fail=1
}

files() { git ls-files -z "$@" | tr '\0' '\n' | grep -Ev "$ALLOW" | grep -Ev '^dev/ci/check-secrets\.sh$'; }

# 1. Local runtime configuration must never be tracked.
for f in bin/ancoragoh.conf .env; do
  if git ls-files --error-unmatch "$f" >/dev/null 2>&1; then report tracked-local-config "$f"; fi
done

# 2. Properties-style configs: password = "<anything but empty or CHANGE_ME>".
hits=$(files '*.conf' '*.conf.example' '*.ini' | xargs -r grep -nIiE '^\s*(db_?)?pass(word|wd)?\s*=\s*"([^"]+)"' 2>/dev/null \
       | grep -vE '=\s*"(CHANGE_ME)?"\s*$' | cut -d: -f1,2)
[ -n "$hits" ] && report properties-password "$hits"

# 3. PHP assignments of a literal to a password-like variable, e.g. $dbpass = 'x';
hits=$(files '*.php' '*.conf' '*.tpl' '*.lib' | xargs -r grep -nIiE "\\\$(db_?)?pass(word|wd)?\s*=\s*['\"][^'\"]+['\"]\s*;" 2>/dev/null | cut -d: -f1,2)
[ -n "$hits" ] && report php-literal-password "$hits"

# 4. libpq connection strings with an inline password (pg_connect, psql URIs, shell scripts).
# (A PHP variable such as $proxypassword = false is a parameter default, not a credential.)
hits=$(files | xargs -r grep -nIiP "(?<![\$\w])password\s*=\s*[^ \"'\$){},]+" 2>/dev/null \
       | grep -iE 'pg_connect|host\s*=|dbname\s*=|postgres(ql)?://' | cut -d: -f1,2)
[ -n "$hits" ] && report inline-connection-password "$hits"
hits=$(files | xargs -r grep -nIE "postgres(ql)?://[^:/ ]+:[^@/ ]+@" 2>/dev/null | cut -d: -f1,2)
[ -n "$hits" ] && report uri-password "$hits"
hits=$(files '*.sh' '*.bat' '*.yml' '*.yaml' | xargs -r grep -nIE "PGPASSWORD=[^ \$]+" 2>/dev/null | cut -d: -f1,2)
[ -n "$hits" ] && report pgpassword-literal "$hits"

# 5. Debug query logs written into the web root must not be tracked.
hits=$(git ls-files 'www/*.log' 'www/**/*.log')
[ -n "$hits" ] && report tracked-webroot-log "$hits"

if [ "$fail" -eq 0 ]; then echo "check-secrets: OK"; fi
exit "$fail"
