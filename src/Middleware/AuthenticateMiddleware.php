<?php

namespace GoSocket\Wrapper\Middleware;

use Closure;

class AuthenticateMiddleware
{
    /**
     * Handle the middleware
     *
     * @param array $payload
     * @param Closure $next
     * @return mixed
     */
    public function handle(array $payload, Closure $next)
    {
        // Check if auth information is present
        if (!isset($payload['auth']) || !isset($payload['auth']['id'])) {
            throw new \Exception('Authentication required');
        }

        // Verify user exists (optional - depends on your needs)
        $userId = $payload['auth']['id'];
        
        if (config('gosocket.strict_auth', false)) {
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');
            
            if (!class_exists($userModel)) {
                throw new \Exception('User model not found');
            }
            
            $user = $userModel::find($userId);
            
            if (!$user) {
                throw new \Exception('User not found');
            }
        }

        return $next($payload);
    }
}
