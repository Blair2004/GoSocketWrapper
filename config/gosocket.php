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
    'socket_server_url' => env('SOCKET_SERVER_URL', 'ws://localhost:8080'),
    'socket_http_url' => env('SOCKET_HTTP_URL', 'http://localhost:8081'),
    'socket_token' => env('SOCKET_TOKEN', null),
    'socket_signing_key' => env('SOCKET_SIGNINKEY', null),

    /*
    |--------------------------------------------------------------------------
    | Handlers Configuration
    |--------------------------------------------------------------------------
    |
    | Paths where socket handlers are stored. Add additional paths here if you
    | use custom paths with the --path option in socket:make-handler command.
    | Paths are relative to the project root.
    |
    */
    'handlers_paths' => [
        'app/Socket/Handlers',
        // Add custom paths here, for example:
        // 'app/CustomSocket/Handlers',
        // 'app/Modules/Notifications/Handlers',
        // 'packages/system/src/Handlers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Default middleware to apply to all socket handlers
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
        'debug' => env('APP_DEBUG', false),
        'ping_interval' => 20000, // milliseconds
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
