<?php

return [
    'client_id' => env('META_CLIENT_ID'),
    'client_secret' => env('META_CLIENT_SECRET'),
    'redirect_uri' => env('META_REDIRECT_URI'),
    'version' => env('META_API_VERSION', 'v18.0'),
    'graph_url' => 'https://graph.facebook.com/',
];