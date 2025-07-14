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
SOCKET_TOKEN=your-gosocket-token
SOCKET_SIGNINKEY=your-jwt-signing-key
```

**Environment Variables Explained:**

- `SOCKET_SERVER_URL`: WebSocket server URL for client connections
- `SOCKET_HTTP_URL`: HTTP API URL for server-to-server communication
- `SOCKET_TOKEN`: Authentication token for Laravel application to communicate with GoSocket server (server-to-server auth)
- `SOCKET_SIGNINKEY`: JWT signing key used to create secure user session tokens for WebSocket connections (user authentication)

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
