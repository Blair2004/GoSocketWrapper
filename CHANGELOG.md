# Changelog

All notable changes to `gosocket/wrapper` will be documented in this file.

## [1.0.0] - 2024-07-14

### Added
- Initial release of GoSocket Laravel Wrapper
- Socket action generation command
- Automatic action discovery mechanism
- Event broadcasting to socket clients
- JWT authentication for socket connections
- Middleware support (authentication, rate limiting)
- Blade directives for socket client integration
- JavaScript socket client with auto-reconnection
- Private and public channel support
- User table migration for socket_jwt storage
- Comprehensive documentation and examples

### Features
- Command: `php artisan socket:make-action {name}`
- Command: `php artisan socket:handle --payload={path}`
- Blade directives: `@socketClient`, `@socket`, `@socket-client`
- Trait: `InteractsWithSockets` for event broadcasting
- Middleware: `AuthenticateMiddleware`, `RateLimitMiddleware`
- Services: `ActionDiscovery`, `SocketHttpClient`
- Facades: `GoSocket` for manual socket communication

### Configuration
- Socket server URL and token configuration
- Actions path configuration for scanning
- Client-side configuration (debug, ping interval, reconnection)
- Broadcasting configuration
- Middleware configuration

### Security
- JWT token generation on user login
- Private channel access control
- Rate limiting protection
- Authentication middleware enforcement
