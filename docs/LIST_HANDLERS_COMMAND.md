# Socket Handlers List Command

This document demonstrates the new `socket:list-handlers` command that was added to the GoSocket Laravel package.

## Command Usage

The command provides several options to list and inspect socket action handlers:

### Basic Usage

```bash
php artisan socket:list-handlers
```

This will show a basic table with:
- Handler Name
- Class
- Auto Load status

### Detailed Information

```bash
php artisan socket:list-handlers --detailed
```

This shows additional information including:
- Middlewares applied to each handler
- Source (how the handler was discovered)

### Filter Auto-loaded Only

```bash
php artisan socket:list-handlers --only-autoload
```

This shows only handlers that have `autoLoad()` returning `true`.

### Combined Options

```bash
php artisan socket:list-handlers --detailed --only-autoload
```

## What the Command Shows

The command will scan for handlers in the following ways:

1. **Auto-discovery**: Scans directories configured in `config/gosocket.php` under `actions_paths`
2. **Custom Registration**: Shows handlers manually registered via service providers using `app('gosocket.handlers')->push()`

## Handler Requirements

For a class to be detected as a valid socket handler, it must:

1. Implement the `GoSocket\Wrapper\Contracts\SocketAction` interface
2. Have the `autoLoad()` method return `true` (for auto-discovery)
3. Be located in one of the configured action paths
4. **Not be an abstract class** (abstract classes like `BaseAction` are automatically excluded)

## Example Output

```
Scanning for socket action handlers...

Found 4 socket action handler(s):

+------------------+----------------------------------------+-----------+
| Handler Name     | Class                                  | Auto Load |
+------------------+----------------------------------------+-----------+
| authenticate     | GoSocket\Wrapper\Actions\AuthAction   | Yes       |
| join_channel     | GoSocket\Wrapper\Actions\JoinAction   | Yes       |
| ping             | GoSocket\Wrapper\Actions\PingAction    | Yes       |
| custom_order     | App\Socket\Actions\CustomOrderAction  | No        |
+------------------+----------------------------------------+-----------+

Summary:
- Total handlers: 4
- Auto-loaded: 3
- Manual registration: 1

Configuration paths:
  ✓ app/Socket/Actions
  ✓ vendor/gosocket/wrapper/src/Actions

Custom registered handlers: 1
```

## Configuration

The command reads configuration from `config/gosocket.php`:

```php
'actions_paths' => [
    'app/Socket/Actions',
    'vendor/gosocket/wrapper/src/Actions',
],
```

This allows the package to automatically discover both user-defined actions and the package's built-in actions.
