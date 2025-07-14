<?php

namespace GoSocket\Wrapper\Handlers;

class AuthenticateHandler extends BaseHandler
{
    /**
     * Handle the authenticate action
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        // Authentication is typically handled by the socket server
        // This handler can be used for additional authentication logic
        
        $token = $payload['data']['token'] ?? null;
        
        if (!$token) {
            throw new \Exception('Token is required for authentication');
        }

        // Additional validation can be added here
        // For example, checking if the JWT is valid in your application
    }

    /**
     * Get the name of the handler
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return 'authenticate';
    }

    /**
     * Get middleware for this handler
     *
     * @return array
     */
    public function middlewares(): array
    {
        return [
            // Don't require authentication for the authenticate handler itself
        ];
    }
}
