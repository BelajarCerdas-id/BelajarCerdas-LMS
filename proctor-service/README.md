# proctor-service

WebSocket fan-out for exam monitoring. Laravel publishes events to Redis pub/sub
channel `proctor:events`; this service relays them to dashboards and students.
It keeps no state. Clients re-sync from the Laravel snapshot after a reconnect.

## Environment

| Variable | Default | Notes |
|---|---|---|
| `PROCTOR_SECRET` | required | Same value as Laravel `PROCTOR_SECRET`. At least 32 characters. |
| `PROCTOR_BIND` | `127.0.0.1:8090` | Keep on loopback; Nginx proxies `/proctor/ws` to it. |
| `PROCTOR_REDIS_URL` | `redis://127.0.0.1:6379/0` | Must match Laravel `PROCTOR_REDIS_DB`. |
| `PROCTOR_MAX_CONNECTIONS` | `12000` | Connections beyond this get HTTP 503. |
| `RUST_LOG` | `info` | Logging level. |

## Build and test

```bash
cargo test
cargo build --release   # binary: target/release/proctor-service
```

## Run under Supervisor (AAPanel)

Deployed layout on the demo box:

```
/www/wwwroot/proctor-service/proctor-service   # binary
/www/wwwroot/proctor-service/proctor.env       # root:www 640
/www/wwwroot/proctor-service/run.sh            # loads the env file, then execs
```

`run.sh` keeps the secret in one file instead of inside the Supervisor config:

```bash
#!/bin/bash
set -a
. /www/wwwroot/proctor-service/proctor.env
set +a
exec /www/wwwroot/proctor-service/proctor-service
```

AAPanel reads `*.ini` from `/www/server/panel/plugin/supervisor/profile/`:

```ini
[program:proctor]
directory=/www/wwwroot/proctor-service
command=/www/wwwroot/proctor-service/run.sh
user=www
numprocs=1
process_name=%(program_name)s_00
autostart=true
autorestart=true
startsecs=3
startretries=10
stopwaitsecs=10
stdout_logfile=/www/server/panel/plugin/supervisor/log/proctor.out.log
stderr_logfile=/www/server/panel/plugin/supervisor/log/proctor.err.log
```

Then `supervisorctl reread && supervisorctl update`. The program also appears in the AAPanel Supervisor UI.

## Nginx (AAPanel site config)

```nginx
location = /proctor/ws {
    proxy_pass http://127.0.0.1:8090/ws;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_read_timeout 3600s;
}
```

Raise `worker_connections` and `worker_rlimit_nofile` if they are below the expected socket count.

## Measured on the demo box (4 cores)

| Connections | RSS | Fan-out delivery | Latency p50 / p95 |
|---|---|---|---|
| 1,000 | 29 MB | 1000/1000 | 26 / 35 ms |
| 5,000 | 78 MB | 5000/5000 | 123 / 161 ms |
| 10,000 | 151 MB | 10000/10000 | 302 / 781 ms |

Latency was measured with a single-process Python client publishing one event to all
connections, so those figures are an upper bound. `read_buffer_size`/`write_buffer_size`
are 8 KiB; raising them multiplies memory by the connection count.
