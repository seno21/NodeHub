<?php

return [

    'websockify' => [
        // Path/binary websockify.
        'binary' => env('VNC_WEBSOCKIFY_BINARY', 'websockify'),

        // Alamat listen websockify.
        'listen' => env('VNC_WEBSOCKIFY_LISTEN', '0.0.0.0:6080'),

        // URL WebSocket publik yang dipakai browser.
        // Kosongkan agar otomatis diturunkan dari host request + port listen.
        'ws_url' => env('VNC_WS_URL'),

        'token_file' => storage_path('app/vnc-tokens.cfg'),
    ],

    'token_ttl' => (int) env('VNC_TOKEN_TTL', 120),

    'status_timeout' => (float) env('VNC_STATUS_TIMEOUT', 1),

    // Refuse to start sessions when the websockify gateway is down.
    'bridge_check' => (bool) env('VNC_BRIDGE_CHECK', true),

];
