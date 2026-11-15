#!/bin/sh
# Deploy an update to demo.belajarcerdas.id so clients get it instantly.
# Run on the server as root:  sh scripts/deploy.sh
#
# Why no cache clearing is needed:
#  - HTML is served with no-cache/no-store (Laravel) -> browser revalidates every load
#  - JS/CSS are fingerprinted (/build/assets/app-HASH.js) -> new build = new URLs
#  - no CDN in front and no proxy cache in nginx -> nothing in between to purge
set -e
APP=/www/wwwroot/demo.belajarcerdas.id

sudo -u www sh -c "cd $APP && git pull --ff-only && npm run build && php artisan octane:reload"

sleep 2
curl -s -o /dev/null -w "site check: %{http_code} in %{time_total}s\n" http://127.0.0.1:8000/
