#!/usr/bin/env python3
"""Enable upstream keepalive for nginx -> Octane on demo.belajarcerdas.id."""
import sys

CONF = "/www/server/panel/vhost/nginx/demo.belajarcerdas.id.conf"
src = open(CONF).read()

if "upstream octane_demo" in src:
    print("already patched")
    sys.exit(0)

assert 'proxy_pass http://127.0.0.1:8000$suffix;' in src, "proxy_pass not found"
assert 'proxy_set_header Connection "upgrade";' in src, "Connection header not found"

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
open(CONF, "w").write(src)
print("patched OK")
