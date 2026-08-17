<?php

return [
    'ticket_secret' => env('WS_TICKET_SECRET'),
    'ticket_ttl' => env('WS_TICKET_TTL', 30),
    'url' => env('WS_URL', 'ws://127.0.0.1:6001'),
];
