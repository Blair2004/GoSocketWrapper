<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoSocket Client</title>
</head>
<body>
    <script>
        // Configuration from Laravel
        window.goSocketConfig = {
            url: '{{ config("gosocket.socket_server_url") }}',
            debug: {{ config("gosocket.client.debug") ? 'true' : 'false' }},
            pingInterval: {{ config("gosocket.client.ping_interval", 30000) }},
            reconnectAttempts: {{ config("gosocket.client.reconnect_attempts", 5) }},
            reconnectDelay: {{ config("gosocket.client.reconnect_delay", 1000) }},
            @auth
            token: '{{ auth()->user()->socket_jwt ?? "" }}',
            @endauth
            autoConnect: true
        };
    </script>
    <script src="{{ asset('vendor/gosocket/gosocket-client.js') }}"></script>
    
    <script>
        // Example usage
        if (window.goSocket) {
            // Listen for connection events
            goSocket.on('connected', function() {
                console.log('Connected to GoSocket!');
            });
            
            goSocket.on('authenticated', function(data) {
                console.log('Authenticated as user:', data.user_id);
            });
            
            goSocket.on('message', function(data) {
                console.log('Received message:', data);
            });
            
            goSocket.on('error', function(error) {
                console.error('Socket error:', error);
            });
            
            // Auto-join a default channel if authenticated
            goSocket.on('authenticated', function() {
                goSocket.joinChannel('general');
            });
        }
    </script>
</body>
</html>
