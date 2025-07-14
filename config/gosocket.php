<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socket Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the GoSocket server
    |
    */
    'socket_server_url' => $_ENV['SOCKET_SERVER_URL'] ?? 'ws://localhost:8080',
    'socket_http_url' => $_ENV['SOCKET_HTTP_URL'] ?? 'http://localhost:8081',
    'socket_token' => $_ENV['SOCKET_TOKEN'] ?? null,
    'socket_signing_key' => $_ENV['SOCKET_SIGNINKEY'] ?? null,

    /*
    |--------------------------------------------------------------------------
    | Actions Configuration
    |--------------------------------------------------------------------------
    |
    | Paths where socket actions are stored (comma separated)
    |
    */
    'actions_paths' => [
        'app/Socket/Actions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Default middleware to apply to all socket actions
    |
    */
    'middlewares' => [
        // Default middleware will be resolved at runtime
    ],

    /*
    |--------------------------------------------------------------------------
    | Socket Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the JavaScript socket client
    |
    */
    'client' => [
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'ping_interval' => 30000, // milliseconds
        'reconnect_attempts' => 5,
        'reconnect_delay' => 1000, // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Broadcasting
    |--------------------------------------------------------------------------
    |
    | Configuration for broadcasting events to socket clients
    |
    */
    'broadcasting' => [
        'enabled' => true,
        'endpoint' => '/api/broadcast',
    ],
];
