#!/bin/bash
# ============================================================
# BelajarCerdas demo IMPORT (run on the NEW demo server)
#
# Expects an export produced by export-demo.sh:
#   export_<date>/data.sql.gz  files.tar.gz  manifest.tsv  files.md5
#
# What it does:
#   1. migrates the fresh schema (optionally --fresh to wipe first)
#   2. loads data.sql with FK/unique checks off
#   3. unpacks files into the new storage layout
#   4. php artisan storage:link
#   5. verifies: row counts vs manifest, file checksums, dangling path refs
#
# Usage: bash import-demo.sh /path/to/export_<date>/ [LARAVEL_ROOT] [--fresh]
# Default LARAVEL_ROOT: /www/wwwroot/demo.belajarcerdas.id
# ============================================================
set -euo pipefail

SRC=${1:?usage: import-demo.sh <export_dir> [laravel_root] [--fresh]}
APP=${2:-/www/wwwroot/demo.belajarcerdas.id}
FRESH=${3:-}

cd "$APP"

# ---- DB credentials from .env ----------------------------------------------
dbenv() { grep -E "^$1=" .env | tail -1 | cut -d= -f2- | tr -d '"'; }
DB=$(dbenv DB_DATABASE); USER=$(dbenv DB_USERNAME); PASS=$(dbenv DB_PASSWORD); HOST=$(dbenv DB_HOST || echo 127.0.0.1)
MYSQL=$(command -v mysql || echo /www/server/mysql/bin/mysql)
MYSQL_ARGS=(--host="$HOST" --user="$USER" --password="$PASS" "$DB")

echo "== 1/5 schema..."
if [ "$FRESH" = "--fresh" ]; then
    php artisan migrate:fresh --force
else
    php artisan migrate --force
fi

echo "== 2/5 data load..."
{ echo "SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0; SET AUTOCOMMIT=0;"
  gunzip -c "$SRC/data.sql.gz"
  echo "COMMIT; SET FOREIGN_KEY_CHECKS=1; SET UNIQUE_CHECKS=1;"
} | "$MYSQL" "${MYSQL_ARGS[@]}"

echo "== 3/5 files..."
tar -xzf "$SRC/files.tar.gz" -C storage/app
mkdir -p storage/app/public public
# strip the archive prefixes: archive dirs are layout tags, not destination names
[ -d storage/app/private ] && cp -a storage/app/private/. storage/app/ && rm -rf storage/app/private
[ -d storage/app/webroot ] && cp -a storage/app/webroot/. public/ && rm -rf storage/app/webroot

# the storage symlink Laravel needs to serve /storage/...
php artisan storage:link >/dev/null 2>&1 || true
chown -R www:www storage public 2>/dev/null || true
chmod -R ug+rX storage/app

echo "== 4/5 file checksums..."
if (cd storage/app && md5sum -c --status "$SRC/files.md5" 2>/dev/null); then
    echo "   all exported files intact"
else
    (cd storage/app && md5sum -c "$SRC/files.md5" 2>/dev/null | grep -v ': OK' || true)
    echo "   (files.md5 may reference webroot/ paths -> checking there)"
    (cd public && md5sum -c "$SRC/files.md5" 2>/dev/null | grep ': OK' | wc -l | xargs echo "   webroot OK files:" ) || true
fi

echo "== 5/5 verification..."
FAILED=0

# a. row counts vs manifest
"$MYSQL" "${MYSQL_ARGS[@]}" -N -e \
  "SELECT table_name FROM information_schema.tables WHERE table_schema='$DB' AND table_name NOT IN
   ('migrations','cache','cache_locks','sessions','jobs','job_batches','failed_jobs','password_reset_tokens','users')" \
  | while read -r t; do printf '%s\t%s\n' "$t" "$("$MYSQL" "${MYSQL_ARGS[@]}" -N -e "SELECT COUNT(*) FROM \`$t\`")"; done \
  > /tmp/import_counts.tsv
python3 - "$SRC/manifest.tsv" /tmp/import_counts.tsv <<'PYEOF' || FAILED=1
import sys
expected = {l.split('\t')[0]: int(l.rstrip('\n').split('\t')[1]) for l in open(sys.argv[1]) if '\t' in l and not l.startswith('table')}
actual   = {l.split('\t')[0]: int(l.rstrip('\n').split('\t')[1]) for l in open(sys.argv[2]) if '\t' in l}
bad = 0
for t, n in expected.items():
    a = actual.get(t, 0)
    if a != n:
        print(f"MISMATCH {t}: expected {n}, got {a}"); bad = 1
print("row counts:", "ALL MATCH" if not bad else f"{sum(1 for t in expected if actual.get(t,0)!=expected[t])} tables differ")
sys.exit(bad)
PYEOF

# b. file-path columns must resolve to real files
cat > /tmp/check_dangling.py <<'PYEOF'
import os, subprocess, sys
checks = [
    ("library_books", "file",        ["storage/app/public/library/file", "storage/app/public/library/video"]),
    ("library_books", "cover",       ["storage/app/public/library/sampul"]),
    ("lms_content_items", "value_file", ["storage/app/public/lms-contents"]),
    ("school_partners", "logo",      ["storage/app/public/school-logo"]),
    ("school_foundations", "logo",   ["storage/app/public/school-foundation-logo"]),
    ("academic_documents", "file_path", ["storage/app/public"]),
    ("student_project_submissions", "file_path", ["storage/app/assessment/assessment-file-submission"]),
]
dangling = 0
def db_rows(sql):
    out = subprocess.run([os.environ.get("MYSQL","mysql"), "-N", "-e", sql],
                         capture_output=True, text=True)
    return [l for l in out.stdout.splitlines() if l.strip()]
for table, col, roots in checks:
    rows = db_rows(f"SELECT {col} FROM {table} WHERE {col} IS NOT NULL AND {col} != ''")
    for v in rows:
        v = v.strip().strip('"')
        name = v.split('/')[-1]
        if v.startswith('http'): continue
        if not any(os.path.exists(os.path.join(r, name)) for r in roots):
            print(f"DANGLING {table}.{col}: {v}"); dangling += 1
print("dangling file refs:", "NONE" if dangling == 0 else dangling)
sys.exit(1 if dangling else 0)
PYEOF

# give the python helper the mysql path; it queries the DB itself
export MYSQL
python3 /tmp/check_dangling.py || FAILED=1

echo
if [ "$FAILED" = 0 ]; then echo "IMPORT OK"; else echo "IMPORT FINISHED WITH WARNINGS — review above"; fi
