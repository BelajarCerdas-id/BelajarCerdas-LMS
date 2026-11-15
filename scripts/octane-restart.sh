#!/bin/sh
# Restart Laravel Octane on the demo server. Octane is managed by aaPanel
# supervisor (program: laravel-octane-demo, config in
# /www/server/panel/plugin/supervisor/profile/laravel-octane-demo.ini).
# Tuned flags live in that ini; use supervisor for start/stop so frankenphp
# is killed with its parent (stopasgroup/killasgroup).
# Run as root on the server, or: ssh www-admin@82.197.69.63 'sudo -S ...'
SUP=/www/server/panel/pyenv/bin/supervisorctl
$SUP restart laravel-octane-demo:laravel-octane-demo_00
sleep 3
$SUP status
curl -s -o /dev/null -w "site check: %{http_code} in %{time_total}s\n" http://127.0.0.1:8000/
