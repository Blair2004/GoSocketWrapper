# List Handlers Command Implementation

## Overview

Added a new Artisan command `socket:list-handlers` to the GoSocket Laravel package that lists all scanned socket action handlers.

## Files Created/Modified

### New Files

1. **`src/Console/Commands/ListHandlersCommand.php`**
   - Main command implementation
   - Provides options for detailed view and filtering
   - Shows summary information including totals and configuration

2. **`tests/Feature/ListHandlersCommandTest.php`**
   - Test coverage for the new command
   - Tests all command options and output formatting

3. **`docs/LIST_HANDLERS_COMMAND.md`**
   - Detailed documentation for the command usage

### Modified Files

1. **`src/GoSocketServiceProvider.php`**
   - Added ListHandlersCommand to the commands array
   - Enhanced the 'gosocket.handlers' singleton to automatically register package actions
   - Auto-discovers actions in the package's Actions directory

2. **`src/Console/Commands/MakeActionCommand.php`**
   - Added `--path` option to specify custom action storage location
   - Enhanced `getDefaultNamespace` to handle custom paths
   - Maintains backward compatibility with default `app/Socket/Actions` location

3. **`config/gosocket.php`**
   - Updated documentation to explain how to add custom action paths
   - Added examples for custom path configuration

4. **`README.md`**
   - Added documentation for the new command and its options
   - Included usage examples for custom paths
   - Added environment variables explanation

## Command Features

### Basic Functionality
- Lists all discovered socket action handlers
- Shows handler name, class, and auto-load status
- Provides summary statistics

### Options
- `--detailed`: Shows additional information (middlewares, source)
- `--only-autoload`: Filters to show only auto-loaded handlers
- Options can be combined

### Output Information
- **Handler Name**: Custom name from `getName()` or class basename
- **Class**: Full class name
- **Auto Load**: Whether the handler is auto-loaded
- **Middlewares**: (detailed mode) Applied middleware
- **Source**: (detailed mode) How the handler was discovered

### Summary Section
- Total handlers count
- Auto-loaded vs manually registered counts
- Configuration paths with existence check
- Custom registered handlers count

## Handler Discovery

The command discovers handlers through two mechanisms:

1. **Auto-discovery**: Scans directories configured in `actions_paths`
2. **Custom Registration**: Shows handlers registered via service providers

### Abstract Class Filtering

The implementation properly filters out abstract classes (like `BaseAction`) at multiple levels:

- **ActionDiscovery Service**: The `isValidAction()` method checks for abstract classes before validation
- **Service Provider**: Auto-registration excludes abstract classes when scanning package actions
- **ListHandlersCommand**: Additional safety check to skip abstract classes during listing

This ensures that only concrete, instantiable handler classes are considered valid handlers.

### Package Actions

The service provider now automatically registers all actions in the package's `src/Actions` directory, so the built-in actions (PingAction, AuthenticateAction, etc.) will always appear in the list.

## Usage Examples

```bash
# Basic list
php artisan socket:list-handlers

# Detailed information
php artisan socket:list-handlers --detailed

# Only auto-loaded handlers
php artisan socket:list-handlers --only-autoload

# Combined options
php artisan socket:list-handlers --detailed --only-autoload
```

## Integration with Existing Code

The command leverages the existing `ActionDiscovery` service and follows the same patterns as other commands in the package. It respects the `SocketAction` interface requirements and the `autoLoad()` method for filtering.

This command provides developers with visibility into which socket handlers are available and how they're being discovered, making debugging and development easier.
