# Shared steps of the end-to-end scheduling run (#25, #32). Sourced by
# dev/e2e/run.sh (PHP built-in server + disposable cluster) and by
# docker/e2e.sh (the Docker Compose deployment). The driver provides:
#
#   work            a private temporary directory
#   base            http://127.0.0.1:<port>/index.php (loopback only)
#   sql QUERY       prints the result of one query (unaligned, tuples only)
#   app_cli SCRIPT ARGS...   runs php bin/SCRIPT from bin/, stdin passed on
#   STEP_TIMEOUT, GEN_TIMEOUT  seconds per request / for the generation
#
# The synthetic organization of dev/e2e/fixture.sql and the tst metrics
# helpers (dev/sql/tests/fixtures/helpers.sql) must already be loaded.

pass=0; fail=0
ok()  { pass=$((pass + 1)); printf 'PASS  %s\n' "$1"; }
bad() { fail=$((fail + 1)); printf 'FAIL  %s\n' "$1"; [ -n "${2:-}" ] && printf '      %s\n' "$2"; }
check() { # description, command...
  local d=$1; shift
  if "$@"; then ok "$d"; else bad "$d"; fi
}
randpw() { od -An -N12 -tx1 /dev/urandom | tr -d ' \n'; }

# curl wrappers: one cookie jar per client; responses are kept for inspection
req() { # jar name, output file, max time, curl args...
  local jar=$work/$1.jar out=$work/$2 max=$3; shift 3
  curl -sS -o "$out" -D "$out.h" -w '%{http_code}' --max-time "$max" -b "$jar" -c "$jar" "$@"
}
event() { # jar, output, max time, event, field=value...
  local jar=$1 out=$2 max=$3 ev=$4; shift 4
  local args=(--data-urlencode "app_event_$ev=yes")
  for kv in "$@"; do args+=(--data-urlencode "$kv"); done
  req "$jar" "$out" "$max" "$base?execute" "${args[@]}"
}
has() { grep -qF -- "$2" "$work/$1"; }
login() { # jar, output, nick, password
  req "$1" "$2" "$STEP_TIMEOUT" "$base" --data-urlencode btnLogin=Entrar --data-urlencode "edtUser=$3" --data-urlencode "edtPass=$4"
}

# Seeded administrator: unusable until bin/setpassword.php sets a password.
# Sets admin_pw.
e2e_set_admin_password() {
  admin_pw=$(randpw)
  check "seeded administrator has no usable password before setpassword" \
    test "$(sql "SELECT contrasena LIKE '!%' FROM seguridad.usuario WHERE nick = 'admin'")" = t
  printf '%s\n' "$admin_pw" | app_cli setpassword.php -nick admin >"$work/cli.log" 2>&1
  check "bin/setpassword.php sets the administrator password (password_hash)" \
    test "$(sql "SELECT contrasena LIKE '\$2y\$%' FROM seguridad.usuario WHERE nick = 'admin'")" = t
}

# A user without roles, for the access-denial step. Sets plain_pw.
e2e_add_plain_user() {
  plain_pw=$(randpw)
  printf '%s\n' "$plain_pw" | app_cli adduser.php -nick e2e_plain -nombre 'Plain user' -correo plain@example.org >>"$work/cli.log" 2>&1
  check "bin/adduser.php creates a user without roles" \
    test "$(sql "SELECT count(*) FROM seguridad.usuario u WHERE nick = 'e2e_plain' AND NOT EXISTS (SELECT 1 FROM seguridad.rol_de_usuario r WHERE r.usuario = u.id)")" = 1
}

# Placements as plantilla/grupo/teacher name/lugar/dia/turno (teacher ids
# created over HTTP are random per run, names are not).
e2e_placements() {
  sql "SELECT string_agg(a.plantilla || '/' || a.grupo || '/' || p.nombre || '/' || a.lugar || '/' || a.dia || '/' || a.turno, ',' ORDER BY a.plantilla, a.grupo) FROM actividad a JOIN profesor p ON p.id = a.profesor"
}
e2e_expected_digest() { grep -v '^#' "$repo/dev/e2e/expected-placements.md5" 2>/dev/null | head -1; }

# Login, denial, data entry, assignments, generation, readback, logout.
e2e_http_flows() {
  local code n tb row g t start took metrics placements digest

  echo "# http: anonymous"
  code=$(req anon login.html "$STEP_TIMEOUT" "$base")
  check "GET / returns the login form (HTTP $code)" bash -c "[ $code = 200 ] && grep -q 'name=\"edtUser\"' '$work/login.html'"
  check "login page does not offer the anonymous timetable viewer" bash -c "! grep -q 'browsers.publico.show' '$work/login.html'"
  code=$(event anon anon-viewer.html "$STEP_TIMEOUT" get_browser_publico access=public)
  check "anonymous viewer event is refused (session expired)" has anon-viewer.html 'message-session-expired'
  code=$(event anon anon-add.html "$STEP_TIMEOUT" add_Profesor field_Profesor_nombre=Intruder)
  n=$(sql "SELECT count(*) FROM profesor WHERE nombre = 'Intruder'")
  check "anonymous data change is refused and writes nothing" \
    bash -c "grep -qF 'message-session-expired' '$work/anon-add.html' && [ '$n' = 0 ]"

  echo "# http: login"
  code=$(login admin wrong.html admin "wrong-$admin_pw")
  check "wrong password is denied" has wrong.html 'access_denied'
  local sid_before sid_after
  sid_before=$(awk '$6 == "PHPSESSID" {print $7}' "$work/admin.jar")
  code=$(login admin app.html admin "$admin_pw")
  sid_after=$(awk '$6 == "PHPSESSID" {print $7}' "$work/admin.jar")
  check "administrator logs in with the password set by setpassword (HTTP $code)" \
    bash -c "[ $code = 200 ] && ! grep -q 'access_denied' '$work/app.html' && ! grep -q 'name=\"edtUser\"' '$work/app.html'"
  check "session id changes at login" bash -c "[ -n '$sid_after' ] && [ '$sid_before' != '$sid_after' ]"
  check "session cookie is HttpOnly and SameSite=Lax" bash -c "grep -i '^set-cookie: PHPSESSID=' '$work/app.html.h' | tail -1 | grep -qi 'httponly' && grep -i '^set-cookie: PHPSESSID=' '$work/app.html.h' | tail -1 | grep -qi 'samesite=lax'"

  echo "# http: access denial"
  code=$(login plain plain.html e2e_plain "$plain_pw")
  check "user without roles logs in" bash -c "! grep -q 'access_denied' '$work/plain.html'"
  code=$(event plain plain-add.html "$STEP_TIMEOUT" add_Profesor field_Profesor_nombre=Unauthorized)
  n=$(sql "SELECT count(*) FROM profesor WHERE nombre = 'Unauthorized'")
  check "user without permission is denied an event and nothing is written" \
    bash -c "grep -qF 'access_denied' '$work/plain-add.html' && [ '$n' = 0 ]"

  echo "# http: data entry and listing (administrator)"
  code=$(event admin add-teacher.html "$STEP_TIMEOUT" add_Profesor "field_Profesor_nombre=Teacher Beta")
  tb=$(sql "SELECT id FROM profesor WHERE nombre = 'Teacher Beta'")
  check "add_Profesor creates the teacher, owned by the administrator (HTTP $code)" \
    test "$(sql "SELECT count(*) FROM profesor WHERE nombre = 'Teacher Beta' AND creador = 'admin-superuser'")" = 1
  code=$(event admin list-teachers.html "$STEP_TIMEOUT" custom_getui_ExploradorListProfesores)
  check "the teacher list shows the new teacher (HTTP $code)" has list-teachers.html 'Teacher Beta'
  for t in B1 B2; do
    code=$(event admin "assign-$t.html" "$STEP_TIMEOUT" add_ProfesorParaActividad "field_ProfesorParaActividad_profesor=$tb" "field_ProfesorParaActividad_plantilla=$t" field_ProfesorParaActividad_prioridad=1)
  done
  check "add_ProfesorParaActividad assigns the teacher to templates B1 and B2" \
    test "$(sql "SELECT count(*) FROM profesor_para_actividad WHERE profesor = '$tb' AND plantilla IN ('B1', 'B2')")" = 2
  for row in $(sql "SELECT id FROM profesor_para_actividad WHERE profesor = '$tb' ORDER BY plantilla"); do
    for g in G1 G2; do
      code=$(event admin "pxg-$row-$g.html" "$STEP_TIMEOUT" custom_ActualizaProfesorParaGrupo "pxact=$row" "grupo=$g")
    done
  done
  check "custom_ActualizaProfesorParaGrupo assigns both groups to both assignments" \
    test "$(sql "SELECT count(*) FROM profesor_para_grupo g JOIN profesor_para_actividad a ON a.id = g.profesor_para_actividad WHERE a.profesor = '$tb'")" = 4

  echo "# http: generation"
  start=$(date +%s)
  code=$(event admin generate.html "$GEN_TIMEOUT" generar esp=E nivel=N periodo=PER forma=1 parche=false compactar=0 parche1=false buscalugar=false)
  took=$(( $(date +%s) - start ))
  check "generar request completes within ${GEN_TIMEOUT}s (HTTP $code, ${took}s)" test "$code" = 200
  metrics=$(sql "SELECT tst.metrics('E', 'N', 'PER')" | sed 's/^METRIC //')
  echo "      METRIC $metrics"
  check "every demand is placed: 8 of 8, no collision, no overflow" test "$metrics" = "demands=8 placed=8 unplaced=0 collisions=0 overflow=0"
  check "every placement has a teacher and a room of its template" \
    test "$(sql "SELECT count(*) FROM actividad a WHERE NOT EXISTS (SELECT 1 FROM profesor_para_actividad p WHERE p.plantilla = a.plantilla AND p.profesor = a.profesor) OR NOT EXISTS (SELECT 1 FROM lugar_para_actividad l WHERE l.plantilla = a.plantilla AND l.lugar = a.lugar)")" = 0
  placements=$(e2e_placements)
  printf '%s\n' "$placements" | tr ',' '\n' | sed 's/^/        /'
  digest=$(printf '%s' "$placements" | md5sum | cut -d' ' -f1)
  echo "      placement digest $digest"
  check "placements are deterministic (dev/e2e/expected-placements.md5)" test "$digest" = "$(e2e_expected_digest)"

  e2e_readback admin "$tb"

  echo "# http: anonymous viewer after generation, logout"
  code=$(event anon anon-tt.html "$STEP_TIMEOUT" get_minibrowser_publico idbrg=B1 idperiodo=PER access=public)
  check "anonymous timetable request is still refused" bash -c "grep -qF 'message-session-expired' '$work/anon-tt.html' && ! grep -qF 'Subject Alpha' '$work/anon-tt.html'"
  e2e_logout admin
}

e2e_readback() { # jar of a logged-in administrator, id of Teacher Beta
  local code
  echo "# http: timetable readback"
  code=$(event "$1" tt-B1.html "$STEP_TIMEOUT" get_minibrowser_horarios idbrg=B1 idperiodo=PER)
  check "group timetable for B1 lists both subjects and both teachers (HTTP $code)" \
    bash -c "for w in 'Subject Alpha' 'Subject Beta' 'Teacher Alpha' 'Teacher Beta'; do grep -qF \"\$w\" '$work/tt-B1.html' || exit 1; done"
  code=$(event "$1" tt-T.html "$STEP_TIMEOUT" get_minibrowser_horarios "idprofe=$2" idperiodo=PER)
  check "teacher timetable for Teacher Beta shows its 4 activities" \
    test "$(grep -oF 'Subject Beta' "$work/tt-T.html" | wc -l)" -ge 4
}

e2e_logout() { # jar
  local code
  code=$(req "$1" logout.html "$STEP_TIMEOUT" "$base?logout")
  code=$(event "$1" after-logout.html "$STEP_TIMEOUT" custom_getui_ExploradorListProfesores)
  check "after logout the session no longer runs events" has after-logout.html 'message-session-expired'
}

e2e_log_checks() { # file with the PHP error log, file with the PostgreSQL log
  local php_bad php_notice sql_err
  php_bad=$(grep -E 'PHP (Warning|Fatal error|Parse error|Recoverable fatal error)|TypeError|ArgumentCountError|Uncaught' "$1" | sort | uniq -c | sort -rn)
  check "no PHP warning, fatal error or TypeError in the web server log" test -z "$php_bad"
  [ -n "$php_bad" ] && printf '%s\n' "$php_bad" | head -20 | sed 's/^/      /'
  php_notice=$(grep -cE 'PHP (Notice|Deprecated)' "$1")
  echo "      PHP notices/deprecations (reported, not failing): $php_notice"
  grep -E 'PHP (Notice|Deprecated)' "$1" | sed -E 's/^\[[^]]*\] //' | sort | uniq -c | sort -rn | head -10 | sed 's/^/        /'
  sql_err=$(grep -E 'ERROR:|FATAL:|PANIC:' "$2" | sort | uniq -c)
  check "no SQL error in the PostgreSQL server log" test -z "$sql_err"
  [ -n "$sql_err" ] && printf '%s\n' "$sql_err" | head -20 | sed 's/^/      /'
}
