#!/bin/bash
# ============================================================
# BelajarCerdas demo EXPORT (run on the OLD demo server)
#
# Produces ./export_<date>/ containing:
#   data.sql.gz        - data-only dump (schema comes from migrations on import)
#   files.tar.gz       - all uploaded files, pre-arranged for the new layout
#   manifest.tsv       - per-table exact row counts (import verification)
#   files.md5          - checksum manifest of every exported file
#
# Usage: bash export-demo.sh [LARAVEL_ROOT]
# Default LARAVEL_ROOT: /www/wwwroot/demo.belajarcerdas.id
# ============================================================
set -euo pipefail

APP=${1:-/www/wwwroot/demo.belajarcerdas.id}
OUT=export_$(date +%Y-%m-%d_%H%M)
mkdir -p "$OUT"

cd "$APP"

# ---- DB credentials from .env (no separate mysql login needed) --------------
dbenv() { grep -E "^$1=" .env | tail -1 | cut -d= -f2- | tr -d '"'; }
DB=$(dbenv DB_DATABASE); USER=$(dbenv DB_USERNAME); PASS=$(dbenv DB_PASSWORD); HOST=$(dbenv DB_HOST || echo 127.0.0.1)
MYSQLDUMP=$(command -v mysqldump || echo /www/server/mysql/bin/mysqldump)

# ---- Exclusions -------------------------------------------------------------
# dead tables (0 rows, dropped from the new schema) + framework/regenerable tables
EXCLUDES=(
  prota_documents analysis_documents rppm_documents teacher_reflections
  topik_materis transactions academic_document_hierarchies
  migrations cache cache_locks sessions
  jobs job_batches failed_jobs password_reset_tokens users
)
IGNORE_ARGS=()
for t in "${EXCLUDES[@]}"; do IGNORE_ARGS+=(--ignore-table="${DB}.${t}"); done

echo "== 1/4 data dump (data-only, complete-insert)..."
"$MYSQLDUMP" --host="$HOST" --user="$USER" --password="$PASS" \
  --single-transaction --no-create-info --complete-insert --skip-triggers \
  --default-character-set=utf8mb4 \
  "${IGNORE_ARGS[@]}" "$DB" > "$OUT/data.sql"

# strip the password-on-command-line warning mysqldump writes to the stream on some versions
sed -i '/Warning: Using a password/d' "$OUT/data.sql" 2>/dev/null || true

echo "== 2/4 row-count manifest (exact, from data.sql)..."
python3 - "$OUT/data.sql" > "$OUT/manifest.tsv" <<'PYEOF'
import sys, re
counts = {}
order = []
cur = None
with open(sys.argv[1], encoding='utf-8', errors='replace') as fh:
    for line in fh:
        m = re.match(r"INSERT INTO `([a-z_]+)` VALUES", line)
        if m:
            cur = m.group(1)
            counts.setdefault(cur, 0)
            if cur not in order: order.append(cur)
            continue
        if cur is not None and line.startswith('('):
            counts[cur] += 1
print("table\trows")
for t in order:
    print(f"{t}\t{counts[t]}")
PYEOF

gzip -f "$OUT/data.sql"

echo "== 3/4 files (pre-arranged for the new layout)..."
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
# public disk content -> storage/app/public on the new install
mkdir -p "$TMP/public"
for d in library lms-contents school-logo school-foundation-logo academic-documents rppm-word refleksi-guru analisis-rppm; do
    [ -d "storage/app/public/$d" ] && cp -a "storage/app/public/$d" "$TMP/public/"
done
# webroot static zone that stays put
mkdir -p "$TMP/webroot"
[ -d public/lms-docx-image ] && cp -a public/lms-docx-image "$TMP/webroot/"
# legacy private zones lived in the webroot -> land in storage/app/ on the new install
mkdir -p "$TMP/private"
for d in lms-assessment-submission assessment; do
    [ -d "public/$d" ] && cp -a "public/$d" "$TMP/private/"
done

tar -czf "$OUT/files.tar.gz" -C "$TMP" public private webroot

echo "== 4/4 checksums..."
(cd "$OUT" && mkdir -p _files && tar -xzf files.tar.gz -C _files && find _files -type f -exec md5sum {} \; | sed 's|_files/||' > files.md5 && rm -rf _files)

echo "DONE -> $OUT/"
ls -lh "$OUT"
