#!/bin/bash

# GoSocket Wrapper Installation Script

echo "Installing GoSocket Wrapper for Laravel..."

# Install the package
composer require gosocket/wrapper

# Publish configuration
php artisan vendor:publish --provider="GoSocket\\Wrapper\\GoSocketServiceProvider" --tag="config"

# Publish assets
php artisan vendor:publish --provider="GoSocket\\Wrapper\\GoSocketServiceProvider" --tag="assets"

# Run migration
php artisan migrate

echo "GoSocket Wrapper installed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure your .env file with socket server settings"
echo "2. Add socket client to your Blade templates using @socketClient"
echo "3. Create socket actions using: php artisan socket:make-action ActionName"
echo ""
echo "Environment variables to add to .env:"
echo "SOCKET_SERVER_URL=ws://localhost:8080"
echo "SOCKET_HTTP_URL=http://localhost:8081"
echo "SOCKET_SERVER_TOKEN=your-gosocket-token"
echo "SOCKET_JWT_TOKEN=your-jwt-signing-key"
