<?php

return [

    // Shared with proctor-service (PROCTOR_SECRET there). Must be at least 32 characters.
    'secret' => env('PROCTOR_SECRET'),

    // Browser-facing WebSocket URL (AAPanel Nginx proxies this path to the Rust service).
    'ws_url' => env('PROCTOR_WS_URL', '/proctor/ws'),

    // Token lifetime in seconds; the client reconnects with a fresh token.
    'token_ttl' => 600,

    // Unlock approver must answer within this window; no answer means denied.
    'unlock_window' => 30,

];
