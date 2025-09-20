# GoSocket Laravel Package

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

A comprehensive Laravel package that provides seamless integration with GoSocket WebSocket server, enabling real-time communication features in your Laravel applications.

## Features

- ✅ **Socket Action Generation**: Create socket actions using Artisan commands
- ✅ **Automatic Action Discovery**: Scan specified directories for socket actions
- ✅ **Event Broadcasting**: Dispatch Laravel events to socket clients automatically
- ✅ **JWT Authentication**: Secure socket connections with JWT tokens
- ✅ **Middleware Support**: Apply middleware to socket actions (auth, rate limiting, etc.)
- ✅ **Blade Integration**: Easy socket client integration with Blade directives
- ✅ **Private Channels**: Support for private and public channels
- ✅ **Rate Limiting**: Built-in rate limiting middleware
- ✅ **Ping/Pong**: Automatic connection health monitoring

## Requirements

- PHP >= 7.4
- Laravel >= 8.0
- lcobucci/jwt ^4.0|^5.0

## Installation

1. Install via Composer:
```bash
composer require gosocket/wrapper
```

2. Publish the package configuration:
```bash
php artisan vendor:publish --provider="GoSocket\Wrapper\GoSocketServiceProvider" --tag="config"
```

3. Publish the JavaScript client assets:
```bash
php artisan vendor:publish --provider="GoSocket\Wrapper\GoSocketServiceProvider" --tag="assets"
```

4. Run the migration to add socket_jwt column to users table:
```bash
php artisan migrate
```

5. Configure your environment variables in `.env`:
```env
SOCKET_SERVER_URL=ws://localhost:8080
SOCKET_HTTP_URL=http://localhost:8081
SOCKET_SERVER_TOKEN=your-gosocket-token
SOCKET_JWT_TOKEN=your-jwt-signing-key
```

## Package Structure

```
package/
├── config/
│   └── gosocket.php              # Configuration file
├── database/
│   └── migrations/               # Database migrations
├── examples/
│   └── USAGE_EXAMPLES.md         # Usage examples
├── resources/
│   ├── js/
│   │   └── gosocket-client.js    # JavaScript socket client
│   └── views/
│       └── client.blade.php      # Blade view for socket client
├── src/
│   ├── Actions/                  # Base socket actions
│   ├── Console/Commands/         # Artisan commands
│   ├── Contracts/                # Interfaces
│   ├── Facades/                  # Laravel facades
│   ├── Helpers/                  # Helper classes
│   ├── Listeners/                # Event listeners
│   ├── Middleware/               # Socket middleware
│   ├── Services/                 # Core services
│   ├── Traits/                   # Traits for broadcasting
│   └── GoSocketServiceProvider.php
└── tests/                        # Package tests
```

## Security Features

### JWT Authentication
- Automatic JWT generation on user login
- JWT tokens stored in `users.socket_jwt` column
- Token validation for private channel access

### Middleware Support
- Built-in authentication middleware
- Rate limiting middleware (60 requests/minute by default)
- Custom middleware support for socket actions

### Private Channels
- Authentication required for private channels
- Channel access control validation
- User permission checking

## Commands

### Generate Socket Action
```bash
php artisan socket:make-action OrderUpdateAction
```

### Handle Socket Messages
```bash
php artisan socket:handle --payload=/path/to/payload.json
```

## Event Broadcasting

Events using the `InteractsWithSockets` trait are automatically broadcasted:

```php
use GoSocket\Wrapper\Traits\InteractsWithSockets;

class OrderUpdated
{
    use InteractsWithSockets;
    
    public function broadcastToEveryone(): bool
    {
        return false;
    }
    
    public function broadcastOn()
    {
        return 'orders.' . $this->order->id;
    }
}
```

## Blade Integration

Add socket client to your templates:

```blade
@socketClient
<!-- or -->
@socket
<!-- or -->
@socket-client
```

## JavaScript Client

The package includes a comprehensive JavaScript client with features:

- Automatic reconnection
- Ping/pong health checks  
- Event-driven architecture
- Debug logging
- Channel management
- Authentication handling

```javascript
// The client is automatically initialized as window.goSocket
goSocket.on('connected', function() {
    console.log('Connected to GoSocket!');
});

goSocket.joinChannel('notifications');
goSocket.sendMessage('chat', 'Hello World!');
```

## Manual Socket Communication

Send messages directly to the socket server:

```php
use GoSocket\Wrapper\Facades\GoSocket;

GoSocket::sendToChannel('notifications', 'new_order', $data);
GoSocket::sendToUser(123, 'private_message', $data);
GoSocket::sendGlobal('system_announcement', $data);
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
