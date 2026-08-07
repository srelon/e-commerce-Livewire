<?php

return [
    'ticket_secret' => env('WS_TICKET_SECRET'),
    'ticket_ttl' => env('WS_TICKET_TTL', 30),
];
