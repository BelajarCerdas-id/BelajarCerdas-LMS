#!/usr/bin/env python3
"""Enable upstream keepalive for nginx -> Octane on demo.belajarcerdas.id.

Usage: nginx-keepalive-patch.py [conf-path]

With no argument it patches the live vhost conf. An explicit path is
accepted so the patch can be rehearsed against a copy.
"""
import sys
from datetime import datetime, timezone
from shutil import copy2

CONF = "/www/server/panel/vhost/nginx/demo.belajarcerdas.id.conf"
conf = sys.argv[1] if len(sys.argv) > 1 else CONF
src = open(conf).read()

if "upstream octane_demo" in src:
    print("already patched")
    sys.exit(0)

# explicit preconditions (not asserts: asserts are stripped under python -O,
# which would let the patch run against a conf it does not understand)
for marker in (
    'proxy_pass http://127.0.0.1:8000$suffix;',
    'proxy_set_header Connection "upgrade";',
):
    if marker not in src:
        print(f"error: expected line not found in {conf}: {marker}", file=sys.stderr)
        sys.exit(1)

# back up the original next to it before rewriting in place
stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
backup = f"{conf}.bak.{stamp}"
copy2(conf, backup)
print(f"backup written: {backup}")

# unique names to avoid clashing with panel-wide map/upstream definitions
src = src.replace(
    "server {",
    "upstream octane_demo {\n"
    "    server 127.0.0.1:8000;\n"
    "    keepalive 64;\n"
    "}\n"
    "map $http_upgrade $oc_connection_upgrade {\n"
    "    default upgrade;\n"
    "    ''      '';\n"
    "}\n"
    "server {",
    1,
)
src = src.replace(
    'proxy_pass http://127.0.0.1:8000$suffix;',
    'proxy_pass http://octane_demo$suffix;',
)
src = src.replace(
    'proxy_set_header Connection "upgrade";',
    'proxy_set_header Connection $oc_connection_upgrade;',
)
open(conf, "w").write(src)
print("patched OK")
