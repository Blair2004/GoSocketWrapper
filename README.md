# GoSocket Wrapper for Laravel

A Laravel package that provides seamless integration with GoSocket server.

## Installation

1. Install the package via Composer:

```bash
composer require gosocket/wrapper
```

2. Publish the configuration:

```bash
php artisan vendor:publish --provider="GoSocket\Wrapper\GoSocketServiceProvider" --tag="config"
```

3. Add the following environment variables to your `.env` file:

```env
SOCKET_SERVER_URL=ws://localhost:8080
SOCKET_HTTP_URL=http://localhost:8081
SOCKET_SERVER_TOKEN=your-gosocket-token
SOCKET_JWT_TOKEN=your-jwt-signing-key
```

**Environment Variables Explained:**

- `SOCKET_SERVER_URL`: WebSocket server URL for client connections
- `SOCKET_HTTP_URL`: HTTP API URL for server-to-server communication
- `SOCKET_SERVER_TOKEN`: Authentication token for Laravel application to communicate with GoSocket server (server-to-server auth)
- `SOCKET_JWT_TOKEN`: JWT signing key used to create secure user session tokens for WebSocket connections (user authentication)

4. Run the migration to add the socket_jwt column to your users table:

```bash
php artisan migrate
```

## Usage

z### Creating Socket Handlers

Generate a new socket handler:

```bash
php artisan socket:make-handler OrderUpdateHandler
```

This will create a new handler class in `app/Socket/Handlers/OrderUpdateHandler.php`.

You can also specify a custom path relative to the project root:

```bash
# Create handler in custom path under app
php artisan socket:make-handler OrderUpdateHandler --path=app/CustomSocket/Handlers

# Create handler in modules structure
php artisan socket:make-handler UserNotificationHandler --path=app/Modules/Notifications/Handlers

# Create handler outside app directory
php artisan socket:make-handler SystemHandler --path=packages/system/src/Handlers
```

The `--path` option allows you to organize handlers in different directories while maintaining the proper namespace structure. The path is relative to the project root directory.

**Directory Creation:** If the specified path doesn't exist, it will be automatically created when generating the handler.

**Note:** When using custom paths, make sure to add them to the `handlers_paths` array in `config/gosocket.php` so they can be discovered by the `socket:list-handlers` command and the handler discovery service.

### List Scanned Handlers

View all discovered socket action handlers:

```bash
php artisan socket:list-handlers
```

Use additional options for more details:

```bash
# Show detailed information including middlewares and source
php artisan socket:list-handlers --detailed

# Show only auto-loaded handlers
php artisan socket:list-handlers --only-autoload

# Combine options
php artisan socket:list-handlers --detailed --only-autoload
```

### Handle Socket Messages

Process incoming socket messages:

```bash
php artisan socket:handle --payload=/path/to/payload.json
```

### Using Socket Client in Blade

Add the socket client to your Blade templates:

```blade
@socketClient

<!-- Or use alternative directives -->
@socket
@socket-client
```

### Events

Make your events broadcastable to socket clients by using the `InteractsWithSockets` trait:

```php
use GoSocket\Wrapper\Traits\InteractsWithSockets;

class OrderUpdated
{
    use InteractsWithSockets;
    
    public function broadcastToEveryone()
    {
        return true;
    }
}
```

#### Client-Specific Broadcasting

Any event using the `InteractsWithSockets` trait automatically gets powerful broadcasting methods:

```php
use GoSocket\Wrapper\Traits\InteractsWithSockets;

class OrderUpdatedEvent
{
    use InteractsWithSockets;

    public $order;
    public $message;

    public function __construct($order, $message = 'Order updated')
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function broadcastAs(): string
    {
        return 'order_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => $this->order,
            'message' => $this->message,
            'timestamp' => date('c')
        ];
    }
}
```

#### Available Broadcasting Methods

Every event using `InteractsWithSockets` automatically gets these static methods:

```php
// Broadcast to specific client connection
OrderUpdatedEvent::dispatchToClient('client-123', $order, 'Your order updated');

// Broadcast to specific user (all their connections)
OrderUpdatedEvent::dispatchToUser('user-456', $order);

// Broadcast to all authenticated users
OrderUpdatedEvent::dispatchToAuthenticated($order, 'Order #123 updated');

// Broadcast to all users except one
OrderUpdatedEvent::dispatchToUsersExcept('user-789', $order);

// Broadcast globally to all clients
OrderUpdatedEvent::dispatchToGlobal($order, 'New order received');

// Broadcast to a specific channel
OrderUpdatedEvent::dispatchToChannel('orders', $order);
```

**Key Features:**
- **Automatic**: No need to add methods to each event class
- **Flexible Parameters**: Pass any constructor arguments after the target ID
- **Laravel Integration**: Events appear in Laravel's event system and can have listeners
- **Consistent API**: Same method signatures across all events

#### Broadcasting Methods

The `InteractsWithSockets` trait provides several methods for targeting specific recipients:

```php
// Broadcast to specific client connection
$event->toClient('client-id-123');

// Broadcast to specific user (all their connections)
$event->toUser('user-456');

// Broadcast to all authenticated users
$event->toAuthenticated();

// Broadcast to all users except one
$event->toUsersExcept('user-789');

// Broadcast globally to all clients
$event->toGlobal();
```

#### Using the GoSocket Facade

You can also use the facade for easier broadcasting:

```php
use GoSocket\Wrapper\Facades\GoSocket;

// Broadcast to specific client (returns bool indicating success)
$success = GoSocket::toClient('client-id-123', new OrderUpdated($order));

// Broadcast to specific user (returns bool indicating success)
$success = GoSocket::toUser('user-456', new NotificationEvent($notification));

// Broadcast to all authenticated users
$success = GoSocket::toAuthenticated(new SystemMaintenanceEvent());

// Broadcast globally
$success = GoSocket::toGlobal(new ServerRestartEvent());

// Check if broadcast was successful
if ($success) {
    Log::info('Event broadcasted successfully');
} else {
    Log::error('Failed to broadcast event');
}
```

**Note:** The facade methods return `bool` indicating whether the broadcast was successful. The underlying event will be dispatched through the `socket.broadcast` event system.

#### Practical Example: Authentication Failure

```php
// Create any event class
class AuthenticationFailedEvent
{
    use InteractsWithSockets;

    public $reason;
    public $userInfo;

    public function __construct($reason = 'Authentication failed', $userInfo = [])
    {
        $this->reason = $reason;
        $this->userInfo = $userInfo;
    }

    public function broadcastAs(): string
    {
        return 'authentication_failed';
    }

    public function broadcastWith(): array
    {
        return [
            'reason' => $this->reason,
            'user_info' => $this->userInfo,
            'action' => 'disconnect'
        ];
    }
}

// Usage in your authentication middleware or controller
public function handleWebSocketConnection(Request $request)
{
    $clientId = $request->get('client_id');
    $token = $request->get('token');
    
    if (!$this->isValidToken($token)) {
        // Send authentication failure to specific client
        AuthenticationFailedEvent::dispatchToClient(
            $clientId, 
            'Invalid or expired token'
        );
        
        return response()->json(['error' => 'Authentication failed'], 401);
    }
    
    // Send success to client
    AuthenticationSuccessEvent::dispatchToClient($clientId, 'Welcome!', $user->toArray());
}
```

#### Real-world Usage Examples

```php
// E-commerce order updates
OrderShippedEvent::dispatchToUser($order->user_id, $order);

// System notifications
MaintenanceEvent::dispatchToAuthenticated('System maintenance in 5 minutes');

// User activity broadcasts
UserOnlineEvent::dispatchToUsersExcept($user->id, $user, 'came online');

// Global announcements
SystemAnnouncementEvent::dispatchToGlobal('New feature released!');

// Chat room messages
ChatMessageEvent::dispatchToChannel("room-{$roomId}", $message, $user);
```

#### Broadcasting Types

The package supports these broadcast types:

- **`client`** - Send to specific client connection
- **`user`** - Send to all connections of a specific user
- **`authenticated`** - Send to all authenticated users
- **`user_except`** - Send to all authenticated users except one
- **`global`** - Send to all connected clients
- **`channel`** - Send to specific channel (default behavior)

## JavaScript Client

The package includes a JavaScript client library (`gosocket-client.js`) for WebSocket communication with the GoSocket server. This client provides a simple API for connecting to the server, handling authentication, joining channels, and sending messages.

### Installation

The JavaScript client is automatically included when you use the `@socketClient` directive in your Blade templates. You can also manually include it:

```html
<script src="{{ asset('vendor/gosocket/js/gosocket-client.js') }}"></script>
```

### Basic Usage

#### Manual Initialization

```javascript
// Create a new GoSocket client instance
const socket = new GoSocketClient({
    url: 'ws://localhost:8080',
    token: 'your-jwt-token',
    debug: true,
    autoConnect: true
});

// Connect to the server
socket.connect();
```

#### Configuration Options

```javascript
const socket = new GoSocketClient({
    url: 'ws://localhost:8080',          // WebSocket server URL
    token: 'your-jwt-token',             // JWT token for authentication
    debug: false,                        // Enable debug logging
    pingInterval: 30000,                 // Ping interval in milliseconds
    reconnectAttempts: 5,                // Maximum reconnection attempts
    reconnectDelay: 1000,                // Base delay between reconnection attempts
    autoConnect: true                    // Auto-connect on instantiation
});
```

#### Auto-Initialization

You can also use the auto-initialization feature by setting up a global configuration:

```javascript
// Set global configuration
window.goSocketConfig = {
    url: 'ws://localhost:8080',
    token: 'your-jwt-token',
    debug: true,
    autoConnect: true
};

// The client will automatically initialize and connect
// Access via window.goSocket
```

### Event Handling

The client provides event listeners for various socket events:

```javascript
// Connection events
socket.on('connected', (data) => {
    console.log('Connected to server');
});

socket.on('disconnected', (data) => {
    console.log('Disconnected from server');
});

socket.on('error', (error) => {
    console.error('Socket error:', error);
});

// Authentication events
socket.on('authenticated', (data) => {
    console.log('Authenticated successfully:', data);
});

// Message events
socket.on('message', (data) => {
    console.log('Received message:', data);
});

// Channel events
socket.on('channel_joined', (data) => {
    console.log('Joined channel:', data);
});

socket.on('channel_left', (data) => {
    console.log('Left channel:', data);
});

// Ping/Pong events
socket.on('pong', (data) => {
    console.log('Received pong from server');
});

// Reconnection events
socket.on('max_reconnect_attempts', () => {
    console.log('Maximum reconnection attempts reached');
});
```

### Authentication

```javascript
// Authenticate with a JWT token
socket.authenticate('your-jwt-token');

// Check authentication status
const status = socket.getStatus();
console.log('Authenticated:', status.authenticated);
console.log('User ID:', status.userId);
```

### Channel Management

```javascript
// Join a public channel
socket.joinChannel('public-channel');

// Join a private channel
socket.joinChannel('private-channel', true);

// Leave a channel
socket.leaveChannel('channel-name');
```

### Sending Messages

```javascript
// Send a message to a channel
socket.sendMessage('channel-name', 'Hello, world!');

// Send a message with additional data
socket.sendMessage('channel-name', 'Order updated', {
    order_id: 123,
    status: 'shipped'
});

// Send custom data to the server
socket.send({
    action: 'custom_action',
    data: {
        custom_field: 'value'
    }
});
```

### Connection Management

```javascript
// Connect to the server
socket.connect();

// Disconnect from the server
socket.disconnect();

// Get connection status
const status = socket.getStatus();
console.log('Connection status:', status);
```

### Complete Example

Here's a complete example showing how to use the GoSocket client in a Laravel Blade template:

```blade
@socketClient

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize socket client
    const socket = new GoSocketClient({
        url: '{{ config('gosocket.socket_server_url') }}',
        token: '{{ auth()->user()->socket_jwt ?? '' }}',
        debug: {{ config('app.debug') ? 'true' : 'false' }},
        autoConnect: true
    });

    // Handle connection events
    socket.on('connected', function() {
        console.log('Connected to GoSocket server');
        
        // Join user-specific channel
        socket.joinChannel('user.{{ auth()->id() }}', true);
        
        // Join public notifications channel
        socket.joinChannel('notifications');
    });

    // Handle incoming messages
    socket.on('message', function(data) {
        console.log('Received message:', data);
        
        // Handle different message types
        switch(data.event) {
            case 'OrderUpdated':
                handleOrderUpdate(data.data);
                break;
            case 'NotificationSent':
                showNotification(data.data);
                break;
        }
    });

    // Handle disconnection
    socket.on('disconnected', function() {
        console.log('Disconnected from GoSocket server');
    });

    // Example functions
    function handleOrderUpdate(data) {
        // Update order status in UI
        const orderElement = document.getElementById('order-' + data.order_id);
        if (orderElement) {
            orderElement.querySelector('.status').textContent = data.status;
        }
    }

    function showNotification(data) {
        // Show notification to user
        const notification = document.createElement('div');
        notification.className = 'alert alert-info';
        notification.textContent = data.message;
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => notification.remove(), 5000);
    }

    // Send message example
    function sendOrderUpdate(orderId, status) {
        socket.sendMessage('orders', 'Order status updated', {
            order_id: orderId,
            status: status,
            timestamp: new Date().toISOString()
        });
    }
});
</script>
```

### Error Handling

The client includes built-in error handling and automatic reconnection:

```javascript
socket.on('error', function(error) {
    console.error('Socket error:', error);
    
    // Handle specific error types
    if (error.type === 'authentication_failed') {
        // Redirect to login or refresh token
        window.location.href = '/login';
    }
});

socket.on('max_reconnect_attempts', function() {
    console.log('Could not reconnect to server');
    
    // Show user-friendly message
    alert('Connection lost. Please refresh the page.');
});
```

### Debugging

Enable debug mode to see detailed logging:

```javascript
const socket = new GoSocketClient({
    url: 'ws://localhost:8080',
    debug: true  // Enable debug logging
});

// Debug logs will show:
// - Connection attempts
// - Messages sent and received
// - Authentication status
// - Reconnection attempts
```

### Registering Custom Handlers

You can register custom socket action handlers programmatically using a service provider. This is useful when you want to register handlers from different packages or modules.

#### Option 1: Using a Service Provider

Create a custom service provider to register your handlers:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Socket\Actions\CustomOrderAction;
use App\Socket\Actions\NotificationAction;

class SocketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register custom handlers
        $this->app->make('gosocket.handlers')->push(CustomOrderAction::class);
        $this->app->make('gosocket.handlers')->push(NotificationAction::class);
    }
}
```

Don't forget to register your service provider in `config/app.php`:

```php
'providers' => [
    // Other providers...
    App\Providers\SocketServiceProvider::class,
],
```

#### Option 2: Using the AppServiceProvider

You can also register handlers in your existing `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register custom socket handlers
        if ($this->app->bound('gosocket.handlers')) {
            $handlers = $this->app->make('gosocket.handlers');
            
            $handlers->push(\App\Socket\Actions\CustomAction::class);
            $handlers->push(\MyPackage\Socket\Actions\PackageAction::class);
        }
    }
}
```

#### Option 3: Configuration-based Registration

You can also configure additional action paths in your `config/gosocket.php` file:

```php
'actions_paths' => [
    'app/Socket/Actions',
    'packages/my-package/src/Actions',
    'modules/notifications/Actions',
],
```

The package will automatically discover and register all socket actions in these directories that implement the `SocketAction` interface and have `autoLoad()` returning `true`.

#### Creating Custom Actions

When creating custom actions, make sure they implement the `SocketAction` interface:

```php
<?php

namespace App\Socket\Actions;

use GoSocket\Wrapper\Contracts\SocketAction;

class CustomOrderAction implements SocketAction
{
    public function handle(array $payload): void
    {
        // Your custom logic here
        $orderId = $payload['data']['order_id'] ?? null;
        $userId = $payload['auth']['id'] ?? null;
        
        // Process the order update
    }
    
    public function getName(): ?string
    {
        return 'custom_order_update'; // Custom action name
    }
    
    public function middlewares(): array
    {
        return ['auth', 'throttle:60,1'];
    }
    
    public function autoLoad(): bool
    {
        return true; // Set to false if you want to register manually only
    }
}
```

## Configuration

The package can be configured via the `config/gosocket.php` file:

- `actions_paths`: Directories to scan for socket actions
- `middlewares`: Default middleware to apply to socket actions
- `socket_server_url`: WebSocket server URL
- `socket_http_url`: HTTP server URL for API calls

## License

MIT License
