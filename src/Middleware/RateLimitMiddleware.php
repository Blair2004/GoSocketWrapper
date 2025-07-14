<?php

namespace GoSocket\Wrapper\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class RateLimitMiddleware
{
    /**
     * Maximum requests per minute per user
     */
    protected $maxAttempts = 60;

    /**
     * Time window in minutes
     */
    protected $decayMinutes = 1;

    /**
     * Handle the middleware
     *
     * @param array $payload
     * @param Closure $next
     * @return mixed
     */
    public function handle(array $payload, Closure $next)
    {
        // Get user identifier
        $userId = $payload['auth']['id'] ?? 'anonymous';
        $key = "socket_rate_limit:{$userId}";

        // Get current attempt count
        $attempts = Cache::get($key, 0);

        // Check if rate limit exceeded
        if ($attempts >= $this->maxAttempts) {
            throw new \Exception('Rate limit exceeded. Too many requests.');
        }

        // Increment attempt count
        Cache::put($key, $attempts + 1, now()->addMinutes($this->decayMinutes));

        return $next($payload);
    }

    /**
     * Set custom rate limits
     *
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return $this
     */
    public function setLimits(int $maxAttempts, int $decayMinutes): self
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        
        return $this;
    }
}
