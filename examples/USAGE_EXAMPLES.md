# GoSocket Laravel Integration Examples

## Installation

1. Install the package:
```bash
composer require gosocket/wrapper
```

2. Publish the configuration:
```bash
php artisan vendor:publish --provider="GoSocket\Wrapper\GoSocketServiceProvider" --tag="config"
```

3. Publish the assets:
```bash
php artisan vendor:publish --provider="GoSocket\Wrapper\GoSocketServiceProvider" --tag="assets"
```

4. Run the migration:
```bash
php artisan migrate
```

## Configuration

Add these environment variables to your `.env` file:

```env
SOCKET_SERVER_URL=ws://localhost:8080
SOCKET_HTTP_URL=http://localhost:8081
SOCKET_TOKEN=your-gosocket-token
SOCKET_SIGNINKEY=your-jwt-signing-key
```

## Creating Socket Actions

Generate a new socket action:

```bash
php artisan socket:make-action OrderUpdateAction
```

This creates a file at `app/Socket/Actions/OrderUpdateAction.php`:

```php
<?php

namespace App\Socket\Actions;

use GoSocket\Wrapper\Actions\BaseAction;

class OrderUpdateAction extends BaseAction
{
    public function handle(array $payload): void
    {
        // Handle the order update
        $orderId = $payload['data']['order_id'] ?? null;
        $status = $payload['data']['status'] ?? null;
        $userId = $payload['auth']['id'] ?? null;
        
        // Your logic here
        // Update order, send notifications, etc.
    }
    
    public function getName(): ?string
    {
        return 'order_update';
    }
    
    public function middlewares(): array
    {
        return [
            'auth', // Require authentication
            'throttle:60,1' // Rate limit
        ];
    }
}
```

## Broadcasting Events

Create an event that broadcasts to socket clients:

```php
<?php

namespace App\Events;

use GoSocket\Wrapper\Traits\InteractsWithSockets;

class OrderStatusChanged
{
    use InteractsWithSockets;
    
    public $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function broadcastToEveryone(): bool
    {
        return false; // Only broadcast to specific channels
    }
    
    public function broadcastOn()
    {
        return 'orders.' . $this->order->id;
    }
    
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'updated_at' => $this->order->updated_at,
        ];
    }
}
```

Dispatch the event:

```php
event(new OrderStatusChanged($order));
```

## Using in Blade Templates

Add the socket client to your layout:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>
</head>
<body>
    <div id="app">
        <!-- Your content -->
    </div>
    
    @socketClient
    
    <script>
        // Listen for order updates
        goSocket.on('authenticated', function() {
            goSocket.joinChannel('orders.123'); // Join specific order channel
        });
        
        goSocket.on('message', function(data) {
            console.log('Received message:', data);
            // Handle the message in your UI
        });
    </script>
</body>
</html>
```

## Manual Socket Communication

You can also send messages directly to the socket server:

```php
use GoSocket\Wrapper\Facades\GoSocket;

// Send to a specific channel
GoSocket::sendToChannel('notifications', 'new_notification', [
    'message' => 'You have a new notification',
    'type' => 'info'
]);

// Send to a specific user
GoSocket::sendToUser(123, 'private_message', [
    'from' => 'Admin',
    'message' => 'Hello!'
]);

// Send global broadcast
GoSocket::sendGlobal('system_announcement', [
    'message' => 'System maintenance in 10 minutes'
]);
```

## Processing Socket Messages

The package automatically handles incoming socket messages. When GoSocket forwards a message to Laravel, it uses:

```bash
php artisan socket:handle --payload=/path/to/payload.json
```

The payload structure:
```json
{
    "action": "order_update",
    "data": {
        "order_id": 123,
        "status": "completed"
    },
    "auth": {
        "id": 456,
        "email": "user@example.com"
    }
}
```

## Custom Middleware

Create custom middleware for socket actions:

```php
<?php

namespace App\Socket\Middleware;

use Closure;

class ValidateOrderAccess
{
    public function handle(array $payload, Closure $next)
    {
        $orderId = $payload['data']['order_id'] ?? null;
        $userId = $payload['auth']['id'] ?? null;
        
        // Check if user has access to this order
        $order = Order::where('id', $orderId)
                     ->where('user_id', $userId)
                     ->first();
                     
        if (!$order) {
            throw new Exception('Access denied to order');
        }
        
        return $next($payload);
    }
}
```

Add it to your action:

```php
public function middlewares(): array
{
    return [
        \App\Socket\Middleware\ValidateOrderAccess::class
    ];
}
```
